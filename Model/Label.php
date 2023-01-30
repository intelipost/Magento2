<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\LabelInterface;
use Intelipost\Shipping\Model\ResourceModel\Label as LabelResource;
use Magento\Framework\Model\AbstractModel;

class Label extends AbstractModel implements LabelInterface
{

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'intelipost_labels';

    /**
     * @var string
     */
    protected $_cacheTag = 'intelipost_labels';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'intelipost_labels';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(LabelResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @inheirtDoc
     */
    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    /**
     * @inheritDoc
     */
    public function setUrl($cfop)
    {
        $this->setData(self::URL, $cfop);
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

