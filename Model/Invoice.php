<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\InvoiceInterface;
use Intelipost\Shipping\Model\ResourceModel\Invoice as InvoiceResource;
use Magento\Framework\Model\AbstractModel;

class Invoice extends AbstractModel implements InvoiceInterface
{

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'intelipost_invoices';

    /**
     * @var string
     */
    protected $_cacheTag = 'intelipost_invoices';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'intelipost_invoices';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(InvoiceResource::class);
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
     * @inheritDoc
     */
    public function getNumber()
    {
        return $this->getData(self::NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setNumber($number)
    {
        $this->setData(self::NUMBER, $number);
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
    public function setOrderIncrementId($orderNumber)
    {
        $this->setData(self::ORDER_INCREMENT_ID, $orderNumber);
    }

    /**
     * @inheritDoc
     */
    public function getSeries()
    {
        return $this->getData(self::SERIES);
    }

    /**
     * @inheritDoc
     */
    public function setSeries($series)
    {
        $this->setData(self::SERIES, $series);
    }

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->getData(self::KEY);
    }

    /**
     * @inheritDoc
     */
    public function setKey($key)
    {
        $this->setData(self::KEY, $key);
    }

    /**
     * @inheritDoc
     */
    public function getDate()
    {
        return $this->getData(self::DATE);
    }

    /**
     * @inheritDoc
     */
    public function setDate($date)
    {
        $this->setData(self::DATE, $date);
    }

    /**
     * @inheritDoc
     */
    public function getTotalValue()
    {
        return $this->getData(self::TOTAL_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setTotalValue($totalValue)
    {
        $this->setData(self::TOTAL_VALUE, $totalValue);
    }

    /**
     * @inheritDoc
     */
    public function getProductsValue()
    {
        return $this->getData(self::PRODUCTS_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setProductsValue($productsValue)
    {
        $this->setData(self::PRODUCTS_VALUE, $productsValue);
    }

    /**
     * @inheritDoc
     */
    public function getCfop()
    {
        return $this->getData(self::CFOP);
    }

    /**
     * @inheritDoc
     */
    public function setCfop($cfop)
    {
        $this->setData(self::CFOP, $cfop);
    }
}

