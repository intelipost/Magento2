<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Api\Data;

interface NfeImportResultInterface
{
    const SUCCESS = 'success';
    const MESSAGE = 'message';
    const INVOICE_ID = 'invoice_id';
    const NFE_NUMBER = 'nfe_number';
    const NFE_KEY = 'nfe_key';

    /**
     * Get success status
     *
     * @return bool
     * @api
     */
    public function getSuccess();

    /**
     * Set success status
     *
     * @param bool $success
     * @return $this
     * @api
     */
    public function setSuccess($success);

    /**
     * Get message
     *
     * @return string
     * @api
     */
    public function getMessage();

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     * @api
     */
    public function setMessage($message);

    /**
     * Get invoice ID
     *
     * @return int|null
     * @api
     */
    public function getInvoiceId();

    /**
     * Set invoice ID
     *
     * @param int $invoiceId
     * @return $this
     * @api
     */
    public function setInvoiceId($invoiceId);

    /**
     * Get NFe number
     *
     * @return string|null
     * @api
     */
    public function getNfeNumber();

    /**
     * Set NFe number
     *
     * @param string $nfeNumber
     * @return $this
     * @api
     */
    public function setNfeNumber($nfeNumber);

    /**
     * Get NFe key
     *
     * @return string|null
     * @api
     */
    public function getNfeKey();

    /**
     * Set NFe key
     *
     * @param string $nfeKey
     * @return $this
     * @api
     */
    public function setNfeKey($nfeKey);
}
