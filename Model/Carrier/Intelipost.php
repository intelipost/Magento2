<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Carrier;

use Intelipost\Shipping\Helper\Api;
use Intelipost\Shipping\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;

class Intelipost extends AbstractCarrier implements CarrierInterface
{
    const LOG = 'intelipost.log';

    protected $_code = 'intelipost';

    /** @var \Psr\Log\LoggerInterface  */
    protected $logger;

    /** @var ResultFactory */
    protected $rateResultFactory;

    /** @var MethodFactory */
    protected $rateMethodFactory;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var Data  */
    protected $helper;

    /** @var Api  */
    protected $api;

    /** @var SourceRepositoryInterface  */
    protected $sourceRepository;

    /** @var ProductRepository */
    protected $productRepository;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param Data $helper
     * @param Api $api
     * @param ProductRepository $productRespository
     * @param SourceRepositoryInterface $sourceRepository
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ProductRepository $productRespository,
        SourceRepositoryInterface $sourceRepository,
        Api $api,
        Data $helper,
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRespository;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->sourceRepository = $sourceRepository;
        $this->logger = $logger;

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
        $originZipcode = $request->getOriginZipcode() ? $request->getOriginZipcode() : $this->getConfigData('source_zip');
        if ($this->getConfigData('use_default_source')) {
            $source = $this->getConfigData('source');
            if ($source) {
                /** @var \Magento\InventoryApi\Api\Data\SourceInterface $sourceModel */
                $sourceModel = $this->sourceRepository->get($source);
                if ($sourceModel && $sourceModel->getPostcode()) {
                    $originZipcode = $sourceModel->getPostcode();
                }
            }
        }

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
                $product = $this->productRepository->getById($item->getProductId());
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
        $result = $this->rateResultFactory->create();

        $resultQuotes = [];

        // API
        try {
            $this->logger->debug('Enviando solicitação para a API');

            $response = $this->api->quoteRequest(
                \Intelipost\Shipping\Client\Intelipost::POST,
                Api::QUOTE_BY_PRODUCT,
                $postData
            );

            $intelipostQuoteId = $response['content']['id'];

            $this->logger->debug('Resposta recebida da API');
        } catch (\Exception $e) {
            $error = $this->_rateErrorFactory->create();
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
            $method = $this->rateMethodFactory->create();

            // Risk Area
            $deliveryNote = isset($child['delivery_note']) ? $child['delivery_note'] : null;
            if (!empty($deliveryNote)) {
                $error = $this->_rateErrorFactory->create();

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

                $child['available_scheduling_dates'] = $this->helper->serializeData($response['content']['available_business_days']);
            }

            // Data
            $deliveryMethodId = $child['delivery_method_id'];
            $child['delivery_method_id'] = $this->_code . '_' . $deliveryMethodId;

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

            $child['delivery_estimate_business_days'] = ($estimateDeliveryDate && $deliveryEstimateDateExactISO)
                ? $child ['delivery_estimate_business_days']
                : $deliveryEstimateBusinessDays;

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
