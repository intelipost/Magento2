<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client\ShipmentOrder;

use Intelipost\Shipping\Model\ResourceModel\Invoice\CollectionFactory;

class Invoice
{
    /** @var CollectionFactory  */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param $intelipostNumber
     * @return \stdClass
     */
    public function getInformation($intelipostNumber)
    {
        $invoiceCollection = $this->getInvoiceCollection($intelipostNumber);
        $invoiceObj = new \stdClass();
        foreach ($invoiceCollection as $invoice) {
            $invoiceDate = $invoice->getData('date');

            $invoiceObj->invoice_number = $invoice->getData('number');
            $invoiceObj->invoice_series = $invoice->getData('series');
            $invoiceObj->invoice_key = $invoice->getData('key');
            $invoiceObj->invoice_date = strtotime($invoiceDate);
            $invoiceObj->invoice_date_iso = str_replace(' ', 'T', (string) $invoice->getData('date'));
            $invoiceObj->invoice_total_value = $invoice->getData('total_value');
            $invoiceObj->invoice_products_value = $invoice->getData('products_value');
            $invoiceObj->invoice_cfop = $invoice->getData('cfop');
        }
        return $invoiceObj;
    }

    /**
     * @param $intelipostNumber
     * @return \Intelipost\Shipping\Model\ResourceModel\Invoice\Collection
     */
    public function getInvoiceCollection($intelipostNumber)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('intelipost_shipment_id', $intelipostNumber);
        return $collection;
    }
}
