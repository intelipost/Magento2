<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface InvoiceInterface
{

    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const NUMBER = 'number';
    const ORDER_INCREMENT_ID = 'order_increment_id';

    const INTELIPOST_SHIPMENT_ID = 'intelipost_shipment_id';
    const SERIES = 'series';
    const KEY = 'key';
    const DATE = 'date';
    const TOTAL_VALUE = 'total_value';
    const PRODUCTS_VALUE = 'products_value';
    const CFOP = 'cfop';

    /**
     * Get item id
     *
     * @return int|null
     * @api
     */
    public function getEntityId();

    /**
     * Set item id
     *
     * @param int $id
     * @return void
     * @api
     */
    public function setEntityId($id);

    /**
     * Get invoice number
     *
     * @return int|null
     * @api
     */
    public function getNumber();

    /**
     * Set invoice number
     *
     * @param int $id
     * @return void
     * @api
     */
    public function setNumber($number);

    /**
     * Get order number
     *
     * @return string|null
     * @api
     */
    public function getOrderIncrementId();

    /**
     * Set order number
     *
     * @param string $orderNumber
     * @return void
     * @api
     */
    public function setOrderIncrementId($orderNumber);

    /**
     * Get invoice series
     *
     * @return string|null
     * @api
     */
    public function getSeries();

    /**
     * Set invoice series
     *
     * @param string $series
     * @return void
     * @api
     */
    public function setSeries($series);

    /**
     * Get invoice key
     *
     * @return string|null
     * @api
     */
    public function getKey();

    /**
     * Set invoice key
     *
     * @param string $key
     * @return void
     * @api
     */
    public function setKey($key);

    /**
     * Get invoice date
     *
     * @return string|null
     * @api
     */
    public function getDate();

    /**
     * Set operation time
     *
     * @param string $date
     * @return void
     * @api
     */
    public function setDate($date);

    /**
     * Get invoice total value
     *
     * @return string|null
     * @api
     */
    public function getTotalValue();

    /**
     * Set invoice total value
     *
     * @param string $totalValue
     * @return void
     * @api
     */
    public function setTotalValue($totalValue);

    /**
     * Get invoice products value
     *
     * @return string|null
     * @api
     */
    public function getProductsValue();

    /**
     * Set invoice products value
     *
     * @param string $productsValue
     * @return void
     * @api
     */
    public function setProductsValue($productsValue);

    /**
     * Get invoice cfop
     *
     * @return string|null
     * @api
     */
    public function getCfop();

    /**
     * Set invoice cfop
     *
     * @param string $cfop
     * @return void
     * @api
     */
    public function setCfop($cfop);

}
