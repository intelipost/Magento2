<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\NfeImportResultInterface;
use Magento\Framework\DataObject;

class NfeImportResult extends DataObject implements NfeImportResultInterface
{
    /**
     * @inheritdoc
     */
    public function getSuccess()
    {
        return (bool)$this->getData(self::SUCCESS);
    }

    /**
     * @inheritdoc
     */
    public function setSuccess($success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @inheritdoc
     */
    public function getInvoiceId()
    {
        return $this->getData(self::INVOICE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setInvoiceId($invoiceId)
    {
        return $this->setData(self::INVOICE_ID, $invoiceId);
    }

    /**
     * @inheritdoc
     */
    public function getNfeNumber()
    {
        return $this->getData(self::NFE_NUMBER);
    }

    /**
     * @inheritdoc
     */
    public function setNfeNumber($nfeNumber)
    {
        return $this->setData(self::NFE_NUMBER, $nfeNumber);
    }

    /**
     * @inheritdoc
     */
    public function getNfeKey()
    {
        return $this->getData(self::NFE_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setNfeKey($nfeKey)
    {
        return $this->setData(self::NFE_KEY, $nfeKey);
    }
}
