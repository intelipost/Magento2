<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface QuoteInterface
{
    const ENTITY_ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const CARRIER = 'carrier';
    const SHIPPING_METHOD = 'shipping_method';
    const PRODUCTS = 'products';
    const LOGISTIC_PROVIDER_NAME = 'logistic_provider_name';
    const DESCRIPTION = 'description';
    const DELIVERY_METHOD_ID = 'delivery_method_id';
    const DELIVERY_ESTIMATE_BUSINESS_DAYS = 'delivery_estimate_business_days';
    const AVAILABLE_SCHEDULING_DATES = 'available_scheduling_dates';
    const SELECTED_SCHEDULING_DATES = 'selected_scheduling_dates';
    const SELECTED_SCHEDULING_PERIOD = 'selected_scheduling_period';
    const PROVIDER_SHIPPING_COST = 'provider_shipping_cost';
    const FINAL_SHIPPING_COST = 'final_shipping_cost';
    const API_REQUEST = 'api_request';
    const API_RESPONSE = 'api_response';
    const DELIVERY_EXACT_ESTIMATED_DATE = 'delivery_exact_estimated_date';
    const DELIVERY_METHOD_NAME = 'delivery_method_name';
    const DELIVERY_METHOD_TYPE = 'delivery_method_type';
    const QUOTE_VOLUME = 'quote_volume';
    const ORIGIN_ZIP_CODE = 'origin_zip_code';
    const PUDO_ID = 'pudo_id';
    const PUDO_EXTERNAL_ID = 'pudo_external_id';
    const PICKUP_ADDRESS = 'pickup_address';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param $entityId
     * @return void
     */
    public function setEntityId($entityId);

    /**
     * @return int
     */
    public function getQuoteId();

    /**
     * @param $quoteId
     * @return void
     */
    public function setQuoteId($quoteId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return void
     */
    public function setOrderId($orderId);

    /**
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * @param $orderIncrementId
     * @return void
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * @return string
     */
    public function getCarrier();

    /**
     * @param $carrier
     * @return void
     */
    public function setCarrier($carrier);

    /**
     * @return string
     */
    public function getShippingMethod();

    /**
     * @param $shippingMethod
     * @return void
     */
    public function setShippingMethod($shippingMethod);

    /**
     * @return string
     */
    public function getProducts();

    /**
     * @param $products
     * @return void
     */
    public function setProducts($products);

    /**
     * @return string
     */
    public function getLogisticProviderName();

    /**
     * @param $logisticProviderName
     * @return void
     */
    public function setLogisticProviderName($logisticProviderName);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param $description
     * @return void
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDeliveryMethodId();

    /**
     * @param $deliveryMethodId
     * @return void
     */
    public function setDeliveryMethodId($deliveryMethodId);

    /**
     * @return string
     */
    public function getDeliveryEstimateBusinessDays();

    /**
     * @param $deliveryEstimateBusinessDays
     * @return void
     */
    public function setDeliveryEstimateBusinessDays($deliveryEstimateBusinessDays);

    /**
     * @return string
     */
    public function getAvailableSchedulingDates();

    /**
     * @param $availableSchedulingDates
     * @return void
     */
    public function setAvailableSchedulingDates($availableSchedulingDates);

    /**
     * @return string
     */
    public function getSelectedSchedulingDates();

    /**
     * @param $selectedSchedulingDates
     * @return void
     */
    public function setSelectedSchedulingDates($selectedSchedulingDates);

    /**
     * @return string
     */
    public function getSelectedSchedulingPeriod();

    /**
     * @param $selectedSchedulingPeriod
     * @return void
     */
    public function setSelectedSchedulingPeriod($selectedSchedulingPeriod);

    /**
     * @return string
     */
    public function getProviderShippingCost();

    /**
     * @param $providerShippingCost
     * @return void
     */
    public function setProviderShippingCost($providerShippingCost);

    /**
     * @return string
     */
    public function getFinalShippingCost();

    /**
     * @param $finalShippingCost
     * @return void
     */
    public function setFinalShippingCost($finalShippingCost);

    /**
     * @return string
     */
    public function getApiRequest();

    /**
     * @param $apiRequest
     * @return void
     */
    public function setApiRequest($apiRequest);

    /**
     * @return string
     */
    public function getApiResponse();

    /**
     * @param $apiResponse
     * @return void
     */
    public function setApiResponse($apiResponse);

    /**
     * @return string
     */
    public function getDeliveryExactEstimatedDate();

    /**
     * @param $deliveryExactEstimatedDate
     * @return void
     */
    public function setDeliveryExactEstimatedDate($deliveryExactEstimatedDate);

    /**
     * @return string
     */
    public function getDeliveryMethodName();

    /**
     * @param $deliveryMethodName
     * @return void
     */
    public function setDeliveryMethodName($deliveryMethodName);

    /**
     * @return string
     */
    public function getDeliveryMethodType();

    /**
     * @param $deliveryMethodType
     * @return void
     */
    public function setDeliveryMethodType($deliveryMethodType);

    /**
     * @return string
     */
    public function getQuoteVolume();

    /**
     * @param $quoteVolume
     * @return void
     */
    public function setQuoteVolume($quoteVolume);

    /**
     * @return string
     */
    public function getOriginZipCode();

    /**
     * @param $originZipCode
     * @return void
     */
    public function setOriginZipCode($originZipCode);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     * @return void
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return int|null
     */
    public function getPudoId();

    /**
     * @param $pudoId
     * @return void
     */
    public function setPudoId($pudoId);

    /**
     * @return string|null
     */
    public function getPudoExternalId();

    /**
     * @param $pudoExternalId
     * @return void
     */
    public function setPudoExternalId($pudoExternalId);

    /**
     * @return string|null
     */
    public function getPickupAddress();

    /**
     * @param $pickupAddress
     * @return void
     */
    public function setPickupAddress($pickupAddress);
}
