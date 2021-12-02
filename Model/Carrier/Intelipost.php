<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

namespace Intelipost\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Intelipost extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const LOG = 'intelipost.log';

    protected $logger;
    protected $_code = 'intelipost';

    /** @var \Magento\Shipping\Model\Rate\ResultFactory */
    protected $_rateResultFactory;

    /** @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory */
    protected $_rateMethodFactory;

    /** @var \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory */
    protected $rateErrorFactory;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;
    protected $helper;
    protected $api;

    protected $_shippingFactory;

    /** @var */
    protected $_pdtMinDate;

    /** @var */
    protected $_origin_zipcode;

    /** @var */
    protected $_productFactory;

    /** @var \Magento\Catalog\Model\ProductRepository */
    protected $_productRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Intelipost\Shipping\Helper\Data $helper
     * @param \Intelipost\Shipping\Helper\Api $api
     * @param \Intelipost\Shipping\Model\QuoteFactory $quoteFactory
     * @param \Magento\Catalog\Model\ProductFactory $_productFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRespository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory,
        \Psr\Log\LoggerInterface                                    $logger,
        \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Intelipost\Shipping\Helper\Data                            $helper,
        \Intelipost\Shipping\Helper\Api                             $api,
        \Intelipost\Shipping\Model\QuoteFactory                     $quoteFactory,
        \Magento\Catalog\Model\ProductFactory                       $_productFactory,
        \Magento\Catalog\Model\ProductRepository                    $productRespository,
        array                                                       $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->rateErrorFactory = $rateErrorFactory;

        $this->_productRepository = $productRespository;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;

        $this->logger = $logger;
        $this->_shippingFactory = $quoteFactory;

        $this->productFactory = $_productFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['intelipost' => $this->getConfigData('title')];
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Framework\DataObject|\Magento\Quote\Model\Quote\Address\RateResult\Error|\Magento\Shipping\Model\Rate\Result|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collectRates(RateRequest $request)
    {
        $this->logger->debug("Iniciando collectRates");

        if (!$this->getConfigFlag('active')) {
            $this->logger->debug("Cotação não realizada pois o módulo esta desativo");
            return false;
        } elseif (!$request->getDestPostcode()) {
            $this->logger->warning("CEP de destino não informado");
            return false;
        }

        // Zipcodes
        //@TODO Get Default Source depennding on config
        $originZipcode = $request->getOriginZipcode() ? $request->getOriginZipcode() : $this->getConfigData('source_zip');
        $destPostcode = $request->getDestPostcode();

        $postData = [
            'carrier' => $this->_code,
            'origin_zip_code' => preg_replace('#[^0-9]#', "", $originZipcode),
            'destination_zip_code' => preg_replace('#[^0-9]#', "", $destPostcode),
        ];

        if (strlen($postData['destination_zip_code']) != 8) {
            return false;
        }

        // Default Config
        $heightAttribute = $this->getConfigData('height_attribute');
        $widthAttribute = $this->scopeConfig->getValue('width_attribute');
        $lengthAttribute = $this->getConfigData('length_attribute');

        $weightUnit = $this->getConfigData('weight_unit') == 'gr' ? 1000 : 1;
        $defaultWeight = intval($this->getConfigData('default_weight')) / $weightUnit;

        $defaultHeight = $this->getConfigData('default_height');
        $defaultWidth = $this->getConfigData('default_width');
        $defaultLength = $this->getConfigData('default_length');

        $estimateDeliveryDate = $this->getConfigData('estimate_delivery_date');

        $calendarOnlyCheckout = $this->getConfigData('calendar_only_checkout');
        $pageName = $this->helper->getPageName();

        $breakOnError = $this->getConfigData('break_on_error');
        $valueOnZero = $this->getConfigData('value_on_zero');

        $cartWeight = 0;
        $cartAmount = 0;
        $cartQtys = 0;
        $cartItems = null;

        // Cart Sort Order: simple, bundle, configurable
        $parentSku = null;
        $totalQuoteItems = 0;

        foreach ($request->getAllItems() as $item) {
            try {
                $product = $this->_productRepository->getById($item->getProductId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }

            // Type
            if (
                !strcmp($item->getProductType(), 'configurable')
                || !strcmp($item->getProductType(), 'bundle')
            ) {
                $parentSku = $product->getSku();
                $cartItems[$parentSku] = $item;
                $cartItems[$parentSku]['product'] = $product;
                continue;
            }

            // Configurable
            $heightConfigurable = 0;
            $widthConfigurable = 0;
            $lengthConfigurable = 0;
            $weightConfigurable = 0;
            $qtyConfigurable = 1;

            if (!empty($cartItems[$parentSku])) {
                $heightConfigurable = $cartItems[$parentSku]['product']->getData($heightAttribute);
                $widthConfigurable = $cartItems[$parentSku]['product']->getData($widthAttribute);
                $lengthConfigurable = $cartItems[$parentSku]['product']->getData($lengthAttribute);
                $weightConfigurable = $cartItems[$parentSku]->getWeight() / $weightUnit;
                $qtyConfigurable = $cartItems[$parentSku]->getQty();
            }

            // Simple
            $height = $product->getData($heightAttribute);
            $width = $product->getData($widthAttribute);
            $length = $product->getData($lengthAttribute);
            $weight = $item->getWeight() / $weightUnit; // always kg

            // Price
            $productPrice = $product->getFinalPrice();
            if (!$productPrice) {
                $productPrice = floatval($valueOnZero);
            }

            // Data
            $productFinalHeight = $this->helper->haveData($height, $heightConfigurable, $defaultHeight);
            $productFinalWidth = $this->helper->haveData($width, $widthConfigurable, $defaultWidth);
            $productFinalLength = $this->helper->haveData($length, $lengthConfigurable, $defaultLength);
            $productFinalWeight = $this->helper->haveData($weight, $weightConfigurable, $defaultWeight);

            $productFinalQty = $item->getQty() * $qtyConfigurable;
            $totalQuoteItems += $productFinalQty;
            $cartWeight += $productFinalWeight * $productFinalQty;
            $cartAmount += $productPrice * $productFinalQty;
            $cartQtys += $productFinalQty;

            $postData['products'][] = [
                'weight' => $productFinalWeight,
                'cost_of_goods' => $productPrice,
                'height' => $productFinalHeight,
                'width' => $productFinalWidth,
                'length' => $productFinalLength,
                'quantity' => $productFinalQty,
                'sku_id' => $product->getSku(),
                'id' => $product->getId(),
                'can_group' => true
            ];
        }

        // Additional
        $postData['additional_information'] = $this->helper->getAdditionalInformation($request->getAdditionalInformation());
        $postData['identification'] = $this->helper->getPageIdentification();
        $postData['cart_weight'] = $cartWeight;
        $postData['cart_amount'] = $cartAmount;
        $postData['cart_qtys'] = $cartQtys;
        $postData['seller_id'] = $request->getSellerId() ? $request->getSellerId() : '';

        // Result
        $result = $this->_rateResultFactory->create();

        $resultQuotes = [];

        // API
        try {
            $this->logger->debug('Enviando solicitação para a API');

            $response = $this->api->quoteRequest(
                \Intelipost\Shipping\Client\Intelipost::POST,
                \Intelipost\Shipping\Helper\Api::QUOTE_BY_PRODUCT,
                $postData
            );

            $intelipostQuoteId = $response['content']['id'];

            $this->logger->debug('Resposta recebida da API');
        } catch (\Exception $e) {
            $error = $this->rateErrorFactory->create();
            $specificerrmsg = $this->getConfigData('specificerrmsg');

            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($specificerrmsg ? $specificerrmsg : $e->getMessage());

            if ($breakOnError) {
                return $error;
            }

            $result->append($error);

            return $result;
        }

        // Free Shipping
        $this->helper->checkFreeShipping($response);

        // Volumes
        $volumes = [];
        $volCount = count($response['content']['volumes']);
        $arrayVol = $this->setProductsQuantity($totalQuoteItems, $volCount);
        $count = 0;
        foreach ($response['content']['volumes'] as $volume) {
            $vWeight = $volume['weight'];
            $vWidth = $volume['width'];
            $vHeight = $volume['height'];
            $vLength = $volume['length'];
            $vProductsQuantity = $arrayVol[$count];

            $aux = [
                'weight' => $vWeight,
                'width' => $vWidth,
                'length' => $vLength,
                'height' => $vHeight,
                'products_quantity' => $vProductsQuantity
            ];
            $volumes[] = $aux;
            $count++;
        }

        // Methods
        foreach ($response ['content']['delivery_options'] as $child) {
            $method = $this->_rateMethodFactory->create();

            // Risk Area
            $deliveryNote = isset($child['delivery_note']) ? $child['delivery_note'] : null;
            if (!empty($deliveryNote)) {
                $error = $this->rateErrorFactory->create();

                $riskareamsg = $this->getConfigData('riskareamsg');

                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($riskareamsg ?: $deliveryNote);

                $method->setWarnMessage($riskareamsg ?: $deliveryNote);

                if ($breakOnError) {
                    return $error;
                }

                $result->setError(true);
                $result->append($error);

                // continue;
            }

            $method->setScheduled(false);

            // Scheduling
            $child['available_scheduling_dates'] = null; // new \Zend_Db_Expr('NULL');
            // $schedulingEnabled = @ $child ['scheduling_enabled'];
            $schedulingEnabled = (array_key_exists('scheduling_enabled', $child)) ? $child ['scheduling_enabled'] : false;
            if ($schedulingEnabled) {
                if ($calendarOnlyCheckout && strcmp($pageName, 'checkout')) {
                    continue;
                }

                $response = $this->api->getAvailableSchedulingDates(
                    $originZipcode,
                    $destPostcode,
                    $child['delivery_method_id']
                );

                $child['available_scheduling_dates'] = json_encode($response['content']['available_business_days']);
            }

            // Data
            $deliveryMethodId = $child['delivery_method_id'];
            $child['delivery_method_id'] = $this->_code . '_' . $deliveryMethodId;

            // $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($child['delivery_method_id']);

            $deliveryEstimateBusinessDays = isset($child['delivery_estimate_business_days']) ? $child['delivery_estimate_business_days'] : null;
            $deliveryEstimateDateExactISO = isset($child['delivery_estimate_date_exact_iso']) ? $child['delivery_estimate_date_exact_iso'] : null;
            if ($estimateDeliveryDate && false /* Disabled */) {
                $response = $this->api->getEstimateDeliveryDate(
                    $originZipcode,
                    $destPostcode,
                    $child ['delivery_estimate_business_days']
                );

                $child['delivery_estimate_business_days'] = date('d/m/Y', strtotime($response ['content']['result_iso']));
            } else {
                if ($deliveryEstimateDateExactISO) {
                    $child['delivery_estimate_business_days'] = date('d/m/Y', strtotime($deliveryEstimateDateExactISO));

                    $method->setDeliveryEstimateDateExactIso($deliveryEstimateDateExactISO);
                }
            }

            $child['delivery_estimate_business_days'] = $estimateDeliveryDate && $deliveryEstimateDateExactISO ? $child ['delivery_estimate_business_days'] : $deliveryEstimateBusinessDays;

            $methodTitle = $this->helper->getCustomCarrierTitle(
                $this->_code,
                $child['description'],
                $child['delivery_estimate_business_days'],
                $schedulingEnabled
            );
            $methodDescription = $this->helper->getCustomCarrierTitle(
                $this->_code,
                $child['delivery_method_name'],
                $child['delivery_estimate_business_days'],
                $schedulingEnabled
            );

            $method->setMethodTitle($methodTitle);
            $method->setMethodDescription($methodDescription);
            $method->setDeliveryMethodType($child['delivery_method_type']);

            $child['delivery_estimate_business_days'] = $deliveryEstimateBusinessDays; // preserve
            $amount = $child['final_shipping_cost'];
            $cost = $child['provider_shipping_cost'];

            $method->setPrice($amount);
            $method->setCost($cost);

            // Save
            $resultQuotes[] = $this->helper->saveQuote($this->_code, $intelipostQuoteId, $child, $postData, $volumes);

            $result->append($method);
        }

        $this->helper->saveResultQuotes($resultQuotes);

        return $result;
    }

    public function setProductsQuantity($qtdProducts, $qtdVolumes)
    {
        $arrayVol = [];
        $result = (int)($qtdProducts / $qtdVolumes);
        $remainder = (int)($qtdProducts % $qtdVolumes);

        for ($n = 0; $n < $qtdVolumes; $n++) {
            $arrayVol[$n] = $result;
            if ($remainder > 0) {
                $arrayVol[$n] = $result + 1;
                $remainder--;
            }
        }
        return $arrayVol;
    }

    public function setOriginZipcode($zipcode)
    {
        $this->_origin_zipcode = $zipcode;
    }
}
