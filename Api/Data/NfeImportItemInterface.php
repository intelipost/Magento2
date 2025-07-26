<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface NfeImportItemInterface
{
    const XML_CONTENT = 'xml_content';
    const ORDER_INCREMENT_ID = 'order_increment_id';

    /**
     * Get XML content (base64 encoded)
     *
     * @return string
     * @api
     */
    public function getXmlContent();

    /**
     * Set XML content (base64 encoded)
     *
     * @param string $xmlContent
     * @return $this
     * @api
     */
    public function setXmlContent($xmlContent);

    /**
     * Get order increment ID
     *
     * @return string|null
     * @api
     */
    public function getOrderIncrementId();

    /**
     * Set order increment ID
     *
     * @param string $orderIncrementId
     * @return $this
     * @api
     */
    public function setOrderIncrementId($orderIncrementId);
}
