<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\NfeImportInterface;
use Intelipost\Shipping\Api\InvoiceRepositoryInterface;
use Intelipost\Shipping\Api\Data\NfeImportResultInterfaceFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Psr\Log\LoggerInterface;

class NfeImport implements NfeImportInterface
{
    /**
     * @var NfeXmlParser
     */
    private $nfeXmlParser;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    /**
     * @var NfeImportResultInterfaceFactory
     */
    private $importResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param NfeXmlParser $nfeXmlParser
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param InvoiceFactory $invoiceFactory
     * @param NfeImportResultInterfaceFactory $importResultFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        NfeXmlParser $nfeXmlParser,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceFactory $invoiceFactory,
        NfeImportResultInterfaceFactory $importResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->nfeXmlParser = $nfeXmlParser;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceFactory = $invoiceFactory;
        $this->importResultFactory = $importResultFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function importNfeXml($xmlContent, $orderIncrementId = null)
    {
        try {
            // Decode base64 content
            $decodedXml = base64_decode($xmlContent, true);
            if ($decodedXml === false) {
                throw new LocalizedException(__('Invalid base64 encoded XML content'));
            }
            
            // Trim the decoded XML to remove any leading/trailing whitespace
            $decodedXml = trim($decodedXml);

            // Validate XML
            $this->nfeXmlParser->validateNfeXml($decodedXml);

            // Parse XML
            $nfeData = $this->nfeXmlParser->parseNfeXml($decodedXml);

            // Override order increment ID if provided
            if ($orderIncrementId) {
                $nfeData['order_increment_id'] = $orderIncrementId;
            }

            // Check if invoice already exists with this NFe key
            $existingInvoice = $this->checkExistingInvoice($nfeData['key']);
            if ($existingInvoice) {
                throw new LocalizedException(
                    __('Invoice with NFe key %1 already exists', $nfeData['key'])
                );
            }

            // Create new invoice
            $invoice = $this->invoiceFactory->create();
            $invoice->setOrderIncrementId($nfeData['order_increment_id']);
            $invoice->setSeries($nfeData['series']);
            $invoice->setNumber($nfeData['number']);
            $invoice->setKey($nfeData['key']);
            $invoice->setDate($nfeData['date']);
            $invoice->setTotalValue($nfeData['total_value']);
            $invoice->setProductsValue($nfeData['products_value']);
            $invoice->setCfop($nfeData['cfop']);

            // If order_increment_id contains shipment ID pattern, set it
            if (strpos($nfeData['order_increment_id'], '-') !== false) {
                $invoice->setIntelipostShipmentId($nfeData['order_increment_id']);
                $orderNumber = explode('-', $nfeData['order_increment_id'])[0];
                $invoice->setOrderIncrementId($orderNumber);
            }

            // Save invoice
            $savedInvoice = $this->invoiceRepository->save($invoice);

            $this->logger->info('NFe imported successfully', [
                'nfe_key' => $nfeData['key'],
                'invoice_id' => $savedInvoice->getId()
            ]);

            return $savedInvoice;
        } catch (\Exception $e) {
            $this->logger->error('Error importing NFe XML', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * @inheritdoc
     */
    public function importMultipleNfeXml($items)
    {
        $results = [];

        foreach ($items as $item) {
            $result = $this->importResultFactory->create();

            try {
                $invoice = $this->importNfeXml(
                    $item->getXmlContent(),
                    $item->getOrderIncrementId()
                );

                $result->setSuccess(true)
                    ->setMessage(__('NFe imported successfully'))
                    ->setInvoiceId($invoice->getId())
                    ->setNfeNumber($invoice->getNumber())
                    ->setNfeKey($invoice->getKey());
            } catch (\Exception $e) {
                $result->setSuccess(false)
                    ->setMessage($e->getMessage());
            }

            $results[] = $result;
        }

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function validateNfeXml($xmlContent)
    {
        try {
            // Decode base64 content
            $decodedXml = base64_decode($xmlContent, true);
            if ($decodedXml === false) {
                throw new LocalizedException(__('Invalid base64 encoded XML content'));
            }
            
            // Trim the decoded XML to remove any leading/trailing whitespace
            $decodedXml = trim($decodedXml);

            return $this->nfeXmlParser->validateNfeXml($decodedXml);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Check if invoice with given NFe key already exists
     *
     * @param string $nfeKey
     * @return bool
     */
    private function checkExistingInvoice($nfeKey)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('key', $nfeKey)
                ->create();

            $invoices = $this->invoiceRepository->getList($searchCriteria);
            return $invoices->getTotalCount() > 0;
        } catch (\Exception $e) {
            // If error checking, assume it doesn't exist
            return false;
        }
    }
}
