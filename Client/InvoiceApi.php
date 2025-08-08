<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Helper\Api;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\Invoice;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Psr\Log\LoggerInterface;
use Intelipost\Shipping\Client\Intelipost;

class InvoiceApi
{
    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @param Api $apiHelper
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     */
    public function __construct(
        Api $apiHelper,
        Data $helper,
        LoggerInterface $logger,
        ShipmentCollectionFactory $shipmentCollectionFactory
    ) {
        $this->apiHelper = $apiHelper;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
    }

    /**
     * Send invoice data to Intelipost API
     *
     * @param Invoice $invoice
     * @return bool
     */
    public function sendInvoiceToIntelipost(Invoice $invoice)
    {
        try {
            // Check if there's an Intelipost shipment for this order
            $shipmentId = $this->getIntelipostShipmentId($invoice);
            if (!$shipmentId) {
                $this->logger->info('No Intelipost shipment found for order: ' . $invoice->getOrderIncrementId());
                return true; // Not an error, just no shipment to update
            }

            // Prepare invoice data
            $invoiceData = $this->prepareInvoiceData($invoice);

            // Send to Intelipost API
            $endpoint = 'shipment_order/set_invoice';
            $response = $this->apiHelper->apiRequest(
                Intelipost::POST,
                $endpoint,
                $invoiceData
            );

            $result = $this->helper->unserializeData($response);

            if ($result['status'] == Intelipost::RESPONSE_STATUS_OK) {
                $this->logger->info('Invoice sent successfully to Intelipost', [
                    'shipment_id' => $shipmentId,
                    'invoice_number' => $invoice->getNumber()
                ]);
                return true;
            } else {
                $errorMessage = 'Failed to send invoice to Intelipost';
                if (isset($result['messages'])) {
                    $errors = [];
                    foreach ($result['messages'] as $message) {
                        $errors[] = $message['text'] ?? $message['message'] ?? 'Unknown error';
                    }
                    $errorMessage .= ': ' . implode(', ', $errors);
                }
                $this->logger->error($errorMessage, [
                    'shipment_id' => $shipmentId,
                    'invoice_number' => $invoice->getNumber(),
                    'response' => $result
                ]);
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error('Error sending invoice to Intelipost: ' . $e->getMessage(), [
                'invoice_id' => $invoice->getId(),
                'exception' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get Intelipost shipment ID for the invoice
     *
     * @param Invoice $invoice
     * @return string|null
     */
    protected function getIntelipostShipmentId(Invoice $invoice)
    {
        // First check if invoice has intelipost_shipment_id
        if ($invoice->getIntelipostShipmentId()) {
            return $invoice->getIntelipostShipmentId();
        }

        // Otherwise, look for shipment by order increment ID
        $shipmentCollection = $this->shipmentCollectionFactory->create();
        $shipmentCollection->addFieldToFilter('order_increment_id', $invoice->getOrderIncrementId());
        $shipmentCollection->addFieldToFilter('intelipost_status', ['neq' => 'ERROR']);
        $shipmentCollection->setPageSize(1);

        $shipment = $shipmentCollection->getFirstItem();
        if ($shipment->getId()) {
            // Return the intelipost_shipment_id if available, otherwise use order_increment_id
            return $shipment->getIntelipostShipmentId() ?: $shipment->getOrderIncrementId();
        }

        return null;
    }

    /**
     * Prepare invoice data for API
     *
     * @param Invoice $invoice
     * @return array
     */
    protected function prepareInvoiceData(Invoice $invoice)
    {
        $invoiceDate = $invoice->getDate();
        if ($invoiceDate) {
            $date = new \DateTime($invoiceDate);
            $invoiceDateIso = $date->format('c'); // ISO 8601 format
        } else {
            $invoiceDateIso = (new \DateTime())->format('c');
        }

        return [
            'order_number' => $invoice->getOrderIncrementId(),
            'shipment_order_volume_invoice_array' => [
                [
                    'shipment_order_volume_number' => '1', // Default to volume 1
                    'invoice_series' => (string) $invoice->getSeries(),
                    'invoice_number' => (string) $invoice->getNumber(),
                    'invoice_key' => (string) $invoice->getKey(),
                    'invoice_date' => $invoiceDateIso,
                    'invoice_total_value' => number_format((float) $invoice->getTotalValue(), 2, '.', ''),
                    'invoice_products_value' => number_format((float) $invoice->getProductsValue(), 2, '.', ''),
                    'invoice_cfop' => (string) $invoice->getCfop(),
//                    'invoice_protocol' => (string) $invoice->getInvoiceProtocol(),
//                    'invoice_type' => (string) $invoice->getInvoiceType()
                ]
            ]
        ];
    }
}
