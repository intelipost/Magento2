<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface ShipmentInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const INTELIPOST_SHIPMENT_ID = 'intelipost_shipment_id';
    const DELIVERY_METHOD_ID = 'delivery_method_id';
    const DELIVERY_ESTIMATE_BUSINESS_DAYS = 'delivery_estimate_business_days';
    const SHIPMENT_ORDERS_TYPE = 'shipment_orders_type';
    const SHIPMENT_ORDERS_SUB_TYPE = 'shipment_orders_sub_type';
    const DELIVERY_METHOD_TYPE = 'delivery_method_type';
    const DELIVERY_METHOD_NAME = 'delivery_method_name';
    const DESCRIPTION = 'description';
    const SALES_CHANNEL = 'sales_channel';
    const SOURCE_CODE = 'source_code';
    const PROVIDER_SHIPPING_COSTS = 'provider_shipping_costs';
    const CUSTOMER_SHIPPING_COSTS = 'customer_shipping_costs';
    const INTELIPOST_STATUS = 'intelipost_status';
    const VOLUMES = 'volumes';
    const SCHEDULED = 'scheduled';
    const SCHEDULING_WINDOW_START = 'scheduling_window_start';
    const SCHEDULING_WINDOW_END = 'scheduling_window_end';
    const TRACKING_CODE = 'tracking_code';
    const TRACKING_URL = 'tracking_url';
    const INTELIPOST_MESSAGE = 'intelipost_message';
    const PRODUCTS_IDS = 'products_ids';

    const ORIGIN_ZIP_CODE = 'origin_zip_code';
    const DELIVERY_ESTIMATE_DATE_EXACT_ISO = 'delivery_estimate_date_exact_iso';
    const SELECTED_SCHEDULING_DATE = 'selected_scheduling_date';
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
    public function getIntelipostShipmentId();

    /**
     * @param $intelipostShipmentId
     * @return void
     */
    public function setIntelipostShipmentId($intelipostShipmentId);

    /**
     * @return string
     */
    public function getShipmentOrdersType();

    /**
     * @param $shipmentOrdersType
     * @return void
     */
    public function setShipmentOrdersType($shipmentOrdersType);

    /**
     * @return string
     */
    public function getShipmentOrdersSubType();

    /**
     * @param $shipmentOrdersSubType
     * @return void
     */
    public function setShipmentOrdersSubType($shipmentOrdersSubType);

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
    public function getDeliveryMethodName();

    /**
     * @param $deliveryMethodName
     * @return void
     */
    public function setDeliveryMethodName($deliveryMethodName);

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
    public function getSalesChannel();

    /**
     * @param $salesChannel
     * @return void
     */
    public function setSalesChannel($salesChannel);

    /**
     * @return string
     */
    public function getSourceCode();

    /**
     * @param $sourceCode
     * @return void
     */
    public function setSourceCode($sourceCode);

    /**
     * @return float
     */
    public function getProviderShippingCosts();

    /**
     * @param $providerShippingCosts
     * @return void
     */
    public function setProviderShippingCosts($providerShippingCosts);

    /**
     * @return float
     */
    public function getCustomerShippingCosts();

    /**
     * @param $customerShippingCosts
     * @return void
     */
    public function setCustomerShippingCosts($customerShippingCosts);

    /**
     * @return string
     */
    public function getIntelipostStatus();

    /**
     * @param $intelipostStatus
     * @return void
     */
    public function setIntelipostStatus($intelipostStatus);

    /**
     * @return string
     */
    public function getVolumes();

    /**
     * @param $volumes
     * @return void
     */
    public function setVolumes($volumes);

    /**
     * @return string
     */
    public function getScheduled();

    /**
     * @param $scheduled
     * @return void
     */
    public function setScheduled($scheduled);

    /**
     * @return string
     */
    public function getSchedulingWindowStart();

    /**
     * @param $schedulingWindowStart
     * @return void
     */
    public function setSchedulingWindowStart($schedulingWindowStart);

    /**
     * @return string
     */
    public function getSchedulingWindowEnd();

    /**
     * @param $schedulingWindowEnd
     * @return void
     */
    public function setSchedulingWindowEnd($schedulingWindowEnd);

    /**
     * @return string
     */
    public function getTrackingCode();

    /**
     * @param $trackingCode
     * @return void
     */
    public function setTrackingCode($trackingCode);

    /**
     * @return string
     */
    public function getTrackingUrl();

    /**
     * @param $trackingUrl
     * @return void
     */
    public function setTrackingUrl($trackingUrl);

    /**
     * @return string
     */
    public function getIntelipostMessage();

    /**
     * @param $intelipostMessage
     * @return void
     */
    public function setIntelipostMessage($intelipostMessage);

    /**
     * @return string
     */
    public function getProductsIds();

    /**
     * @param $productsIds
     * @return void
     */
    public function setProductsIds($productsIds);

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
    public function getDeliveryEstimateDateExactIso();

    /**
     * @param $deliveryEstimateDateExactIso
     * @return void
     */
    public function setDeliveryEstimateDateExactIso($deliveryEstimateDateExactIso);

    /**
     * @return string
     */
    public function getSelectedSchedulingDate();

    /**
     * @param $selectedSchedulingDate
     * @return void
     */
    public function setSelectedSchedulingDate($selectedSchedulingDate);

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



}
