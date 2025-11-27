<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\QuoteInterface;
use Intelipost\Shipping\Model\ResourceModel\Quote as ResourceQuote;
use Magento\Framework\Model\AbstractModel;


class Quote extends AbstractModel implements QuoteInterface
{
    /**
     * Unique identifier to be used in caching
     * @var string
     */
    protected $_cacheTag = 'intelipost_quotes';

    /**
     * Prefix for triggered events
     * @var string
     */
    protected $_eventPrefix = 'intelipost_quotes';

    protected function _construct()
    {
        $this->_init(ResourceQuote::class);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId($quoteId)
    {
        $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::ORDER_ID, $orderId);
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
    public function getCarrier()
    {
        return $this->getData(self::CARRIER);
    }

    /**
     * @inheritDoc
     */
    public function setCarrier($carrier)
    {
        $this->setData(self::CARRIER, $carrier);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->setData(self::SHIPPING_METHOD, $shippingMethod);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        return $this->getData(self::PRODUCTS);
    }

    /**
     * @inheritDoc
     */
    public function setProducts($products)
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /**
     * @inheritDoc
     */
    public function getLogisticProviderName()
    {
        return $this->getData(self::LOGISTIC_PROVIDER_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setLogisticProviderName($logisticProviderName)
    {
        $this->setData(self::LOGISTIC_PROVIDER_NAME, $logisticProviderName);
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
    public function getAvailableSchedulingDates()
    {
        return $this->getData(self::AVAILABLE_SCHEDULING_DATES);
    }

    /**
     * @inheritDoc
     */
    public function setAvailableSchedulingDates($availableSchedulingDates)
    {
        $this->setData(self::AVAILABLE_SCHEDULING_DATES, $availableSchedulingDates);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedSchedulingDates()
    {
        return $this->getData(self::SELECTED_SCHEDULING_DATES);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedSchedulingDates($selectedSchedulingDates)
    {
        $this->setData(self::SELECTED_SCHEDULING_DATES, $selectedSchedulingDates);
    }

    /**
     * @inheritDoc
     */
    public function getSelectedSchedulingPeriod()
    {
        return $this->getData(self::SELECTED_SCHEDULING_PERIOD);
    }

    /**
     * @inheritDoc
     */
    public function setSelectedSchedulingPeriod($selectedSchedulingPeriod)
    {
        $this->setData(self::SELECTED_SCHEDULING_PERIOD, $selectedSchedulingPeriod);
    }

    /**
     * @inheritDoc
     */
    public function getProviderShippingCost()
    {
        return $this->getData(self::PROVIDER_SHIPPING_COST);
    }

    /**
     * @inheritDoc
     */
    public function setProviderShippingCost($providerShippingCost)
    {
        $this->setData(self::PROVIDER_SHIPPING_COST, $providerShippingCost);
    }

    /**
     * @inheritDoc
     */
    public function getFinalShippingCost()
    {
        return $this->getData(self::FINAL_SHIPPING_COST);
    }

    /**
     * @inheritDoc
     */
    public function setFinalShippingCost($finalShippingCost)
    {
        $this->setData(self::FINAL_SHIPPING_COST, $finalShippingCost);
    }

    /**
     * @inheritDoc
     */
    public function getApiRequest()
    {
        return $this->getData(self::API_REQUEST);
    }

    /**
     * @inheritDoc
     */
    public function setApiRequest($apiRequest)
    {
        $this->setData(self::API_REQUEST, $apiRequest);
    }

    /**
     * @inheritDoc
     */
    public function getApiResponse()
    {
        return $this->getData(self::API_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setApiResponse($apiResponse)
    {
        $this->setData(self::API_RESPONSE, $apiResponse);
    }

    /**
     * @inheritDoc
     */
    public function getDeliveryExactEstimatedDate()
    {
        return $this->getData(self::DELIVERY_EXACT_ESTIMATED_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setDeliveryExactEstimatedDate($deliveryExactEstimatedDate)
    {
        $this->setData(self::DELIVERY_EXACT_ESTIMATED_DATE, $deliveryExactEstimatedDate);
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
    public function getQuoteVolume()
    {
        return $this->getData(self::QUOTE_VOLUME);
    }

    /**
     * @inheritDoc
     */
    public function setQuoteVolume($quoteVolume)
    {
        $this->setData(self::QUOTE_VOLUME, $quoteVolume);
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

    /**
     * @inheritDoc
     */
    public function getPudoId()
    {
        return $this->getData(self::PUDO_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPudoId($pudoId)
    {
        $this->setData(self::PUDO_ID, $pudoId);
    }

    /**
     * @inheritDoc
     */
    public function getPudoExternalId()
    {
        return $this->getData(self::PUDO_EXTERNAL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPudoExternalId($pudoExternalId)
    {
        $this->setData(self::PUDO_EXTERNAL_ID, $pudoExternalId);
    }

    /**
     * @inheritDoc
     */
    public function getPickupAddress()
    {
        return $this->getData(self::PICKUP_ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function setPickupAddress($pickupAddress)
    {
        $this->setData(self::PICKUP_ADDRESS, $pickupAddress);
    }
}
