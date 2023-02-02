<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Intelipost\Shipping\Api\Data\LabelInterface;
use Intelipost\Shipping\Model\LabelFactory;
use Intelipost\Shipping\Api\Data\LabelSearchResultsInterfaceFactory;
use Intelipost\Shipping\Api\LabelRepositoryInterface;
use Intelipost\Shipping\Model\ResourceModel\Label as ResourceLabel;
use Intelipost\Shipping\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class LabelRepository implements LabelRepositoryInterface
{
    /** @var ResourceLabel  */
    protected $resource;

    /** @var LabelFactory  */
    protected $labelFactory;

    /** @var LabelCollectionFactory  */
    protected $labelCollectionFactory;

    /** @var LabelSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    protected $collectionProcessor;

    /**
     * @param ResourceLabel $resource
     * @param LabelFactory $labelFactory
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param LabelSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceLabel $resource,
        LabelFactory $labelFactory,
        LabelCollectionFactory $labelCollectionFactory,
        LabelSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->labelFactory = $labelFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        LabelInterface $label
    ) {
        try {
            $label = $this->resource->save($label);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Item: %1',
                $exception->getMessage()
            ));
        }
        return $label;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $label = $this->labelFactory->create();
        $this->resource->load($label, $id);
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $label;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        $label = $this->labelFactory->create();
        $this->resource->load($label, $orderId, 'order_increment_id');
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $orderId));
        }
        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->labelCollectionFactory->create();

        $searchResults = $this->searchResultsFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        $items = [];
        /** @var \Intelipost\Shipping\Model\Label $model */
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
        LabelInterface $label
    ) {
        try {
            $labelModel = $this->labelFactory->create();
            $this->resource->load($labelModel, $label->getEntityId());
            $this->resource->delete($labelModel);
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
