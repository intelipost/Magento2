<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Carrier;

use Intelipost\Shipping\Helper\Api;
use Intelipost\Shipping\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;
use Magento\Shipping\Model\Tracking\Result\Error as TrackingError;
use Magento\Shipping\Model\Tracking\Result\ErrorFactory as TrackErrorFactory;
use Magento\Shipping\Model\Tracking\Result\StatusFactory as TrackStatusFactory;
use Magento\Shipping\Model\Tracking\ResultFactory as TrackResultFactory;

class Intelipost extends AbstractCarrier implements CarrierInterface
{
    public const LOG = 'intelipost.log';

    protected $_code = 'intelipost';

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /** @var ResultFactory */
    protected $rateResultFactory;

    /** @var MethodFactory */
    protected $rateMethodFactory;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var Data */
    protected $helper;

    /** @var Api */
    protected $api;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var TrackStatusFactory  */
    protected $trackStatusFactory;

    /**
     * @var TrackResultFactory
     */
    protected $trackResultFactory;

    /**
     * @var TrackErrorFactory
     */
    protected $trackErrorFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        ProductRepository $productRespository,
        TrackStatusFactory $trackStatusFactory,
        TrackResultFactory $trackResultFactory,
        TrackErrorFactory $trackErrorFactory,
        Api $api,
        Data $helper,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRespository;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->trackResultFactory = $trackResultFactory;
        $this->trackErrorFactory = $trackErrorFactory;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->api = $api;
        $this->logger = $logger;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['intelipost' => (string) $this->getConfigData('title')];
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

        $originZipcode = $this->getOriginZipcode($request);

        $breakOnError = $this->getConfigData('break_on_error');
        $destPostcode = $request->getDestPostcode();
        $postData = [
            'carrier' => $this->_code,
            'origin_zip_code' => preg_replace('#[^0-9]#', "", $originZipcode),
            'destination_zip_code' => preg_replace('#[^0-9]#', "", $destPostcode),
        ];

        if (strlen($postData['destination_zip_code']) != 8) {
            return false;
        }

        $calendarOnlyCheckout = $this->getConfigData('calendar_only_checkout');
        $pageName = $this->helper->getPageName();

        $postData = $this->getProductData($request, $postData);

        // Additional
        $postData['additional_information'] = $this->helper->getAdditionalInformation(
            $request->getAdditionalInformation()
        );
        $postData['identification'] = $this->helper->getPageIdentification();
        $postData['seller_id'] = $request->getSellerId() ? $request->getSellerId() : '';
        $postData['api_request'] = $postData;

        // Result
        $result = $this->rateResultFactory->create();

        // Check if pickup is enabled and select endpoint
        $pickupEnabled = (bool) $this->getConfigData('pickup_enabled');
        $apiEndpoint = $pickupEnabled ? Api::QUOTE_PICKUP_BY_PRODUCT : Api::QUOTE_BY_PRODUCT;

        $resultQuotes = [];
        try {
            $this->logger->debug('Enviando solicitação para a API');
            $response = $this->api->quoteRequest(
                \Intelipost\Shipping\Client\Intelipost::POST,
                $apiEndpoint,
                $postData
            );
            $intelipostQuoteId = $response['content']['id'];
            $this->logger->debug('Resposta recebida da API');
        } catch (\Exception $e) {
            $error = $this->_rateErrorFactory->create();
            $specificerrmsg = $this->getConfigData('specificerrmsg');

            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($specificerrmsg ?: $e->getMessage());

            if ($breakOnError) {
                return $error;
            }
            $result->append($error);
            return $result;
        }

        // Free Shipping
        if ((bool) $request->getFreeShipping()) {
            $response = $this->helper->checkFreeShipping($response);
        }

        // Volumes
        $volumes = $this->getVolumes($response, $postData['cart_qty']);
        $riskWarning = false;

        // Risk Area
        $deliveryRiskAreas = (bool) $this->getConfigData('delivery_risk_areas');
        $riskAreaMessage = $this->getConfigData('riskareamsg');

        // Methods
        foreach ($response['content']['delivery_options'] as $child) {
            $deliveryNote = $child['delivery_note'] ?? null;
            if (!empty($deliveryNote) && !$riskWarning) {
                $riskWarning = true;

                /** @var \Magento\Quote\Model\Quote\Address\RateResult\Error $error */
                $error = $this->_rateErrorFactory->create();

                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($riskAreaMessage ?: $deliveryNote);

                $result->setError(true);
                $result->append($error);

                if (!$deliveryRiskAreas) {
                    continue;
                }
            }

            // Check if this is a pickup option with addresses
            $pickupAddresses = $child['pickup_addresses'] ?? [];
            $deliveryMethodType = $child['delivery_method_type'] ?? '';
            $isPickup = $deliveryMethodType === 'PICKUP' && !empty($pickupAddresses);

            if ($isPickup) {
                // Create one shipping method per pickup address
                foreach ($pickupAddresses as $pickupAddress) {
                    $pickupMethod = $this->createPickupMethod(
                        $child,
                        $pickupAddress,
                        $intelipostQuoteId,
                        $postData,
                        $volumes,
                        $resultQuotes
                    );
                    $result->append($pickupMethod);
                }
            } else {
                // Standard delivery method processing
                $method = $this->rateMethodFactory->create();
                $method->setScheduled(false);

                // Scheduling
                $child['available_scheduling_dates'] = null;
                $schedulingEnabled = $child['scheduling_enabled'] ?? false;

                if ($schedulingEnabled && $pageName) {
                    if ($calendarOnlyCheckout && strcmp($pageName, 'checkout')) {
                        continue;
                    }

                    $schedulingResponse = $this->api->getAvailableSchedulingDates(
                        $originZipcode,
                        $destPostcode,
                        $child['delivery_method_id']
                    );

                    $availableBusinessDays = $schedulingResponse['content']['available_business_days'];
                    $child['available_scheduling_dates'] = $this->helper->serializeData($availableBusinessDays);
                }

                // Data
                $deliveryMethodId = $child['delivery_method_id'];
                $child['delivery_method_id'] = $this->_code . '_' . $deliveryMethodId;

                $method->setCarrier($this->_code);
                $method->setCarrierTitle((string) $this->getConfigData('title'));
                $method->setMethod($child['delivery_method_id']);

                $deliveryEstimateBusinessDays = $child['delivery_estimate_business_days'] ?? null;
                $deliveryEstimateDateExactISO = $child['delivery_estimate_date_exact_iso'] ?? null;

                if ($deliveryEstimateDateExactISO) {
                    $method->setDeliveryEstimateDateExactIso($deliveryEstimateDateExactISO);
                }

                $methodTitle = $this->helper->getCustomCarrierTitle(
                    $this->_code,
                    $child['description'],
                    $deliveryEstimateBusinessDays,
                    $schedulingEnabled
                );
                $methodDescription = $this->helper->getCustomCarrierTitle(
                    $this->_code,
                    $child['delivery_method_name'],
                    $deliveryEstimateBusinessDays,
                    $schedulingEnabled
                );

                $method->setMethodTitle($methodTitle);
                $method->setMethodDescription($methodDescription);
                $method->setDeliveryMethodType($child['delivery_method_type']);

                $amount = $child['final_shipping_cost'];
                $cost = $child['provider_shipping_cost'];

                $method->setPrice($amount);
                $method->setCost($cost);

                // Save
                $resultQuotes[] = $this->helper->saveQuote($this->_code, $intelipostQuoteId, $child, $postData, $volumes);

                $result->append($method);
            }
        }

        $this->helper->saveResultQuotes($resultQuotes);

        return $result;
    }

    /**
     * @param $request
     * @param $postData
     * @return array
     */
    public function getProductData($request, $postData): array
    {
        // Default Config
        $heightAttribute = $this->getConfigData('height_attribute');
        $widthAttribute = $this->getConfigData('width_attribute');
        $lengthAttribute = $this->getConfigData('length_attribute');
        $weightUnit = $this->getConfigData('weight_unit') == 'gr' ? 1000 : 1;
        $defaultWeight = intval($this->getConfigData('default_weight')) / $weightUnit;
        $defaultHeight = $this->getConfigData('default_height');
        $defaultWidth = $this->getConfigData('default_width');
        $defaultLength = $this->getConfigData('default_length');
        $valueOnZero = (float) $this->getConfigData('value_on_zero');

        $cartWeight = 0;
        $cartAmount = 0;
        $cartQty = 0;
        $cartItems = [];

        // Cart Sort Order: simple, bundle, configurable
        $parentSku = null;
        foreach ($request->getAllItems() as $item) {
            try {
                $product = $this->productRepository->getById($item->getProductId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }

            // Type
            $isConfigurable = !strcmp((string) $item->getProductType(), 'configurable');
            $isBundle = !strcmp((string) $item->getProductType(), 'bundle');

            if ($isBundle || $isConfigurable) {
                $parentSku = $product->getSku();
                $cartItems[$parentSku] = $item;
                $cartItems[$parentSku]['product'] = $product;

                if ($isBundle) {
                    $currentDate = date('Y-m-d');
                    $fromDate = $product->getSpecialFromDate();
                    $toDate = $product->getSpecialToDate();
                    if ((!$fromDate || $currentDate >= $fromDate) && (!$toDate || $currentDate <= $toDate)) {
                        $cartItems[$parentSku]['special_price'] = $product->getSpecialPrice();
                    }
                }
                continue;
            }

            // Configurable
            $heightConfigurable = 0;
            $widthConfigurable = 0;
            $lengthConfigurable = 0;
            $weightConfigurable = 0;
            $qtyConfigurable = 1;

            if (!empty($cartItems[$parentSku])) {
                if ($heightAttribute) {
                    $heightConfigurable = $cartItems[$parentSku]['product']->getData($heightAttribute);
                }
                if ($widthAttribute) {
                    $widthConfigurable = $cartItems[$parentSku]['product']->getData($widthAttribute);
                }
                if ($lengthAttribute) {
                    $lengthConfigurable = $cartItems[$parentSku]['product']->getData($lengthAttribute);
                }
                $weightConfigurable = $cartItems[$parentSku]->getWeight() / $weightUnit;
                $qtyConfigurable = $cartItems[$parentSku]->getQty();
            }

            // Simple
            $height = ($heightAttribute) ? $product->getData($heightAttribute) : null;
            $width = ($widthAttribute) ? $product->getData($widthAttribute) : null;
            $length = ($lengthAttribute) ? $product->getData($lengthAttribute) : null;
            $weight = $item->getWeight() / $weightUnit; // always kg

            $productPrice = $this->getProductPrice($product->getFinalPrice(), $valueOnZero, $cartItems, $parentSku);

            $productFinalHeight = $this->helper->haveData($height, $heightConfigurable, $defaultHeight);
            $productFinalWidth = $this->helper->haveData($width, $widthConfigurable, $defaultWidth);
            $productFinalLength = $this->helper->haveData($length, $lengthConfigurable, $defaultLength);
            $productFinalWeight = $this->helper->haveData($weight, $weightConfigurable, $defaultWeight);

            $productFinalQty = $item->getQty() * $qtyConfigurable;
            $cartWeight += $productFinalWeight * $productFinalQty;
            $cartAmount += $productPrice * $productFinalQty;
            $cartQty += $productFinalQty;

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

        $postData['cart_weight'] = $cartWeight;
        $postData['cart_amount'] = $cartAmount;
        $postData['cart_qty'] = $cartQty;

        return $postData;
    }

    /**
     * @param $qtdProducts
     * @param $qtdVolumes
     * @return array
     */
    public function setProductsQuantity($qtdProducts, $qtdVolumes): array
    {
        $arrayVol = [];
        $result = (int) ($qtdProducts / $qtdVolumes);
        $remainder = ($qtdProducts % $qtdVolumes);

        for ($n = 0; $n < $qtdVolumes; $n++) {
            $arrayVol[$n] = $result;
            if ($remainder > 0) {
                $arrayVol[$n] = $result + 1;
                $remainder--;
            }
        }
        return $arrayVol;
    }

    /**
     * @param $request
     * @return false|string|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOriginZipcode($request): string
    {
        // Zipcodes
        $originPostCode = $request->getOriginZipcode() ?: (string) $this->getConfigData('source_zip');
        if (!$originPostCode) {
            $originPostCode = (string) $this->helper->getConfig('postcode', 'origin', 'shipping');
        }
        return $originPostCode;
    }

    /**
     * @param $response
     * @param $cartQty
     * @return array
     */
    public function getVolumes($response, $cartQty): array
    {
        $volumes = [];
        $volCount = count($response['content']['volumes']);
        $arrayVol = $this->setProductsQuantity($cartQty, $volCount);

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

        return $volumes;
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    public function getTrackingInfo(string $trackingNumber): array
    {
        return ['number' => $trackingNumber];
    }

    /**
     * @param float $productPrice
     * @param float $valueOnZero
     * @param array $cartItems
     * @param string|null $parentSku
     * @return float
     */
    public function getProductPrice(
        float $productPrice,
        float $valueOnZero,
        array $cartItems,
        ?string $parentSku = null
    ): float {

        if (!$productPrice) {
            return $valueOnZero;
        }

        if (!empty($cartItems[$parentSku]['special_price'])) {
            return round($productPrice * ($cartItems[$parentSku]['special_price'] / 100), 2);
        }

        return $productPrice;
    }

    /**
     * Create a shipping method for a pickup address
     *
     * @param array $deliveryOption
     * @param array $pickupAddress
     * @param string $quoteId
     * @param array $postData
     * @param array $volumes
     * @param array $resultQuotes
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    protected function createPickupMethod(
        array $deliveryOption,
        array $pickupAddress,
        $quoteId,
        array $postData,
        array $volumes,
        array &$resultQuotes
    ) {
        $method = $this->rateMethodFactory->create();

        // Use pudo_id in method code to make it unique
        $pudoId = $pickupAddress['pudo_id'];
        $deliveryMethodId = $deliveryOption['delivery_method_id'];
        $methodCode = $this->_code . '_' . $deliveryMethodId . '_pudo_' . $pudoId;

        $method->setCarrier($this->_code);
        $method->setCarrierTitle((string) $this->getConfigData('title'));
        $method->setMethod($methodCode);

        // Format title with pickup address
        $methodTitle = $this->helper->getCustomPickupTitle(
            $deliveryOption['description'],
            $pickupAddress
        );
        $method->setMethodTitle($methodTitle);
        $method->setMethodDescription($pickupAddress['additional'] ?? '');
        $method->setDeliveryMethodType($deliveryOption['delivery_method_type']);

        $deliveryEstimateDateExactISO = $deliveryOption['delivery_estimate_date_exact_iso'] ?? null;
        if ($deliveryEstimateDateExactISO) {
            $method->setDeliveryEstimateDateExactIso($deliveryEstimateDateExactISO);
        }

        $method->setPrice($deliveryOption['final_shipping_cost']);
        $method->setCost($deliveryOption['provider_shipping_cost']);

        // Prepare data for saving quote with pickup info
        $childData = $deliveryOption;
        $childData['delivery_method_id'] = $methodCode;
        $childData['pudo_id'] = $pudoId;
        $childData['pudo_external_id'] = $pickupAddress['pudo_external_id'] ?? '';
        $childData['pickup_address'] = $pickupAddress;
        $childData['available_scheduling_dates'] = null;

        // Save quote with pickup data
        $resultQuotes[] = $this->helper->saveQuote($this->_code, $quoteId, $childData, $postData, $volumes);

        return $method;
    }
}
