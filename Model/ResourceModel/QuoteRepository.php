<?php
/**
 * @package Biz
 * @author Thiago Contardi
 * @copyright Copyright (c) 2020 Bizcommerce
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Intelipost\Shipping\Api\Data\QuoteInterface;
use Intelipost\Shipping\Model\QuoteFactory;
use Intelipost\Shipping\Api\Data\QuoteSearchResultsInterfaceFactory;
use Intelipost\Shipping\Api\QuoteRepositoryInterface;
use Intelipost\Shipping\Model\ResourceModel\Quote as ResourceQuote;
use Intelipost\Shipping\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteRepository implements QuoteRepositoryInterface
{

    /** @var ResourceQuote  */
    protected $resource;

    /** @var QuoteFactory  */
    protected $quoteFactory;

    /** @var QuoteCollectionFactory  */
    protected $quoteCollectionFactory;

    /** @var QuoteSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    protected $collectionProcessor;

    /**
     * @param ResourceQuote $resource
     * @param QuoteFactory $quoteFactory
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param QuoteSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceQuote $resource,
        QuoteFactory $quoteFactory,
        QuoteCollectionFactory $quoteCollectionFactory,
        QuoteSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    )
    {
        $this->resource = $resource;
        $this->quoteFactory = $quoteFactory;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->quoteFactory = $quoteFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        QuoteInterface $quote
    ) {
        try {
            $quote = $this->resource->save($quote);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Item: %1',
                $exception->getMessage()
            ));
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $quote = $this->quoteFactory->create();
        $this->resource->load($quote, $id);
        if (!$quote->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    )
    {
        $collection = $this->quoteCollectionFactory->create();

        $searchResults = $this->searchResultsFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        $items = [];
        /** @var \Intelipost\Shipping\Model\Quote $model */
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
        QuoteInterface $quote
    )
    {
        try {
            $quoteModel = $this->quoteFactory->create();
            $this->resource->load($quoteModel, $quote->getEntityId());
            $this->resource->delete($quoteModel);
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
