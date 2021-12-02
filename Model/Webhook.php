<?php
/*
 * @package     Intelipost_Push
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\WebhookInterface;
use Intelipost\Shipping\Model\ResourceModel\Webhook as WebhookResource;
use Magento\Framework\Model\AbstractModel;

class Webhook extends AbstractModel implements WebhookInterface
{
    /**
     * Initializes the resource model
     */
    protected function _construct()
    {
        $this->_init(WebhookResource::class);
    }

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'intelipost_webhooks';

    /**
     * @var string
     */
    protected $_cacheTag = 'intelipost_webhooks';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'intelipost_webhooks';

    /**
     * Get OrderId.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set STatus.
     * @param $status
     */
    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
    }


    /**
     * Get OrderId.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set STatus.
     * @param $status
     */
    public function setMessage($status)
    {
        $this->setData(self::MESSAGE, $status);
    }

    /**
     * Get IncrementId.
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * Set IncrementId.
     * @param $incrementId
     */
    public function setOrderIncrementId($incrementId)
    {
        $this->setData(self::ORDER_INCREMENT_ID, $incrementId);
    }

    /**
     * Get Payload.
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->getData(self::PAYLOAD);
    }

    /**
     * Set Payload.
     * @param $payload
     */
    public function setPayload($payload)
    {
        $this->setData(self::PAYLOAD, $payload);
    }

    /**
     * Get CreatedAt.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     * @param $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }
}

