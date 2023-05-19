<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\ShipmentInterface;
use Magento\Framework\Model\AbstractModel;
use Intelipost\Shipping\Model\ResourceModel\Shipment as ShipmentResource;

class Shipment extends AbstractModel implements ShipmentInterface
{
    CONST STATUS_PENDING = 'pending';
    CONST STATUS_ERROR = 'error';
    CONST STATUS_SHIPPED = 'shipped';
    CONST STATUS_READY_FOR_SHIPMENT = 'ready_for_shipment';
    CONST STATUS_CREATED = 'created';

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'intelipost_shipments';

    /**
     * @var string
     */
    protected $_cacheTag = 'intelipost_shipments';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'intelipost_shipments';

    protected function _construct()
    {
        $this->_init(ShipmentResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryMethodId()
    {
        return $this->getData(self::DELIVERY_METHOD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryMethodId($deliveryMethodId)
    {
        $this->setData(self::DELIVERY_METHOD_ID, $deliveryMethodId);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryEstimateBusinessDays()
    {
        return $this->getData(self::DELIVERY_ESTIMATE_BUSINESS_DAYS);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryEstimateBusinessDays($deliveryEstimateBusinessDays)
    {
        $this->setData(self::DELIVERY_ESTIMATE_BUSINESS_DAYS, $deliveryEstimateBusinessDays);
    }

    /**
     * @inheritDoc
     */
    public function getIntelipostShipmentId()
    {
        return $this->getData(self::INTELIPOST_SHIPMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIntelipostShipmentId($intelipostShipmentId)
    {
        $this->setData(self::INTELIPOST_SHIPMENT_ID, $intelipostShipmentId);
    }

    /**
     * @inheritDoc
     */
    public function getShipmentOrdersType()
    {
        return $this->getData(self::SHIPMENT_ORDERS_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setShipmentOrdersType($shipmentOrdersType)
    {
        $this->setData(self::SHIPMENT_ORDERS_TYPE, $shipmentOrdersType);
    }

    /**
     * @inheritDoc
     */
    public function getShipmentOrdersSubType()
    {
        return $this->getData(self::SHIPMENT_ORDERS_SUB_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setShipmentOrdersSubType($shipmentOrdersSubType)
    {
        $this->setData(self::SHIPMENT_ORDERS_SUB_TYPE, $shipmentOrdersSubType);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryMethodType()
    {
        return $this->getData(self::DELIVERY_METHOD_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryMethodType($deliveryMethodType)
    {
        $this->setData(self::DELIVERY_METHOD_TYPE, $deliveryMethodType);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryMethodName()
    {
        return $this->getData(self::DELIVERY_METHOD_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryMethodName($deliveryMethodName)
    {
        $this->setData(self::DELIVERY_METHOD_NAME, $deliveryMethodName);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritDoc
     */
    public function getSalesChannel()
    {
        return $this->getData(self::SALES_CHANNEL);
    }

    /**
     * @inheritDoc
     */
    public function setSalesChannel($salesChannel)
    {
        $this->setData(self::SALES_CHANNEL, $salesChannel);
    }

    /**
     * @inheritDoc
     */
    public function getProviderShippingCosts()
    {
        return $this->getData(self::PROVIDER_SHIPPING_COSTS);
    }

    /**
     * @inheritDoc
     */
    public function setProviderShippingCosts($providerShippingCosts)
    {
        $this->setData(self::PROVIDER_SHIPPING_COSTS, $providerShippingCosts);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerShippingCosts()
    {
        return $this->getData(self::CUSTOMER_SHIPPING_COSTS);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerShippingCosts($customerShippingCosts)
    {
        $this->setData(self::CUSTOMER_SHIPPING_COSTS, $customerShippingCosts);
    }

    /**
     * @inheritDoc
     */
    public function getIntelipostStatus()
    {
        return $this->getData(self::INTELIPOST_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setIntelipostStatus($intelipostStatus)
    {
        $this->setData(self::INTELIPOST_STATUS, $intelipostStatus);
    }

    /**
     * @inheritDoc
     */
    public function getVolumes()
    {
        return $this->getData(self::VOLUMES);
    }

    /**
     * @inheritDoc
     */
    public function setVolumes($volumes)
    {
        $this->setData(self::VOLUMES, $volumes);
    }

    /**
     * @inheritDoc
     */
    public function getScheduled()
    {
        return $this->getData(self::SCHEDULED);
    }

    /**
     * @inheritDoc
     */
    public function setScheduled($scheduled)
    {
        $this->setData(self::SCHEDULED, $scheduled);
    }

    /**
     * @inheritDoc
     */
    public function getSchedulingWindowStart()
    {
        return $this->getData(self::SCHEDULING_WINDOW_START);
    }

    /**
     * @inheritDoc
     */
    public function setSchedulingWindowStart($schedulingWindowStart)
    {
        $this->setData(self::SCHEDULING_WINDOW_START, $schedulingWindowStart);
    }

    /**
     * @inheritDoc
     */
    public function getSchedulingWindowEnd()
    {
        return $this->getData(self::SCHEDULING_WINDOW_END);
    }

    /**
     * @inheritDoc
     */
    public function setSchedulingWindowEnd($schedulingWindowEnd)
    {
        $this->setData(self::SCHEDULING_WINDOW_END, $schedulingWindowEnd);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingCode()
    {
        return $this->getData(self::TRACKING_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setTrackingCode($trackingCode)
    {
        $this->setData(self::TRACKING_CODE, $trackingCode);
    }

    /**
     * @inheritDoc
     */
    public function getTrackingUrl()
    {
        return $this->getData(self::TRACKING_URL);
    }

    /**
     * @inheritDoc
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->setData(self::TRACKING_URL, $trackingUrl);
    }

    /**
     * @inheritDoc
     */
    public function getIntelipostMessage()
    {
        return $this->getData(self::INTELIPOST_MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setIntelipostMessage($intelipostMessage)
    {
        $this->setData(self::INTELIPOST_MESSAGE, $intelipostMessage);
    }

    /**
     * @inheritDoc
     */
    public function getProductsIds()
    {
        return $this->getData(self::PRODUCTS_IDS);
    }

    /**
     * @inheritDoc
     */
    public function setProductsIds($productsIds)
    {
        $this->setData(self::PRODUCTS_IDS, $productsIds);
    }

    /**
     * @inheritDoc
     */
    public function getOriginZipCode()
    {
        return $this->getData(self::ORIGIN_ZIP_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setOriginZipCode($originZipCode)
    {
        $this->setData(self::ORIGIN_ZIP_CODE, $originZipCode);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryEstimateDateExactIso()
    {
        return $this->getData(self::DELIVERY_ESTIMATE_DATE_EXACT_ISO);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryEstimateDateExactIso($deliveryEstimateDateExactIso)
    {
        $this->setData(self::DELIVERY_ESTIMATE_DATE_EXACT_ISO, $deliveryEstimateDateExactIso);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedSchedulingDate()
    {
        return $this->getData(self::SELECTED_SCHEDULING_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedSchedulingDate($selectedSchedulingDate)
    {
        $this->setData(self::SELECTED_SCHEDULING_DATE, $selectedSchedulingDate);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
