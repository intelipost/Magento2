<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api;

interface NfeImportInterface
{
    /**
     * Import NFe XML and save as invoice
     *
     * @param string $xmlContent Base64 encoded XML content
     * @param string|null $orderIncrementId Optional order increment ID
     * @return \Intelipost\Shipping\Api\Data\InvoiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function importNfeXml($xmlContent, $orderIncrementId = null);

    /**
     * Import multiple NFe XMLs
     *
     * @param \Intelipost\Shipping\Api\Data\NfeImportItemInterface[] $items
     * @return \Intelipost\Shipping\Api\Data\NfeImportResultInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function importMultipleNfeXml($items);

    /**
     * Validate NFe XML content
     *
     * @param string $xmlContent Base64 encoded XML content
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function validateNfeXml($xmlContent);
}