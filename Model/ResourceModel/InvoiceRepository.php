<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Intelipost\Shipping\Api\Data\InvoiceInterface;
use Intelipost\Shipping\Model\InvoiceFactory;
use Intelipost\Shipping\Api\Data\InvoiceSearchResultsInterfaceFactory;
use Intelipost\Shipping\Api\InvoiceRepositoryInterface;
use Intelipost\Shipping\Model\ResourceModel\Invoice as ResourceInvoice;
use Intelipost\Shipping\Model\ResourceModel\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class InvoiceRepository implements InvoiceRepositoryInterface
{

    /** @var ResourceInvoice  */
    protected $resource;

    /** @var InvoiceFactory  */
    protected $invoiceFactory;

    /** @var InvoiceCollectionFactory  */
    protected $invoiceCollectionFactory;

    /** @var InvoiceSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    protected $collectionProcessor;

    /**
     * @param ResourceInvoice $resource
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param InvoiceSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceInvoice $resource,
        InvoiceFactory $invoiceFactory,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        InvoiceSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->invoiceFactory = $invoiceFactory;
        $this->quoteFactory = $invoiceFactory;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->quoteFactory = $invoiceFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        InvoiceInterface $invoice
    ) {
        try {
            $invoice = $this->resource->save($invoice);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Item: %1',
                $exception->getMessage()
            ));
        }
        return $invoice;
    }

    /**
     * {@inheritdoc}
     */
    public function saveInvoices($invoices)
    {
        foreach ($invoices as $nfe) {

            /** @var  \Intelipost\Shipping\Model\Invoice $invoice */
            $invoice = $this->invoiceFactory->create();

            $invoice->setId($nfe->getId());
            $invoice->setOrderIncrementId($nfe->getOrderIncrementId());
            $invoice->setSeries($nfe->getSeries());
            $invoice->setNumber($nfe->getNumber());
            $invoice->setKey($nfe->getKey());
            $invoice->setDate($nfe->getDate());
            $invoice->setTotalValue($nfe->getTotalValue());
            $invoice->setProductsValue($nfe->getProductsValue());
            $invoice->setCfop($nfe->getCfop());

            $this->resource->save($invoice);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $invoice = $this->quoteFactory->create();
        $this->resource->load($invoice, $id);
        if (!$invoice->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $invoice;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    )
    {
        $collection = $this->invoiceCollectionFactory->create();

        $searchResults = $this->searchResultsFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        $items = [];
        /** @var \Intelipost\Shipping\Model\Invoice $model */
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;

    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        InvoiceInterface $invoice
    )
    {
        try {
            $invoiceModel = $this->quoteFactory->create();
            $this->resource->load($invoiceModel, $invoice->getEntityId());
            $this->resource->delete($invoiceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Item: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}


