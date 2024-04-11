<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface LabelInterface
{

    /**#@+
     * Constants defined for keys of the data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';

    const URL = 'url';

    const ORDER_INCREMENT_ID = 'order_increment_id';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    /**
     * Get invoice number
     *
     * @return string
     * @api
     */
    public function getUrl();

    /**
     * Set invoice number
     *
     * @param string $url
     * @return void
     * @api
     */
    public function setUrl($url);

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
