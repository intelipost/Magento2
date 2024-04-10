<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Intelipost\Shipping\Api\Data\ShipmentInterface;
use Intelipost\Shipping\Model\ShipmentFactory;
use Intelipost\Shipping\Api\Data\ShipmentSearchResultsInterfaceFactory;
use Intelipost\Shipping\Api\ShipmentRepositoryInterface;
use Intelipost\Shipping\Model\ResourceModel\Shipment as ResourceShipment;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ShipmentRepository implements ShipmentRepositoryInterface
{

    /** @var ResourceShipment  */
    protected $resource;

    /** @var ShipmentFactory  */
    protected $shipmentFactory;

    /** @var ShipmentCollectionFactory  */
    protected $shipmentCollectionFactory;

    /** @var ShipmentSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    protected $collectionProcessor;

    /**
     * @param ResourceShipment $resource
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param ShipmentSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceShipment $resource,
        ShipmentFactory $shipmentFactory,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        ShipmentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        ShipmentInterface $shipment
    ) {
        try {
            $shipment = $this->resource->save($shipment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Item: %1',
                $exception->getMessage()
            ));
        }
        return $shipment;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $shipment = $this->shipmentFactory->create();
        $this->resource->load($shipment, $id);
        if (!$shipment->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $shipment;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getByIntelipostShipmentId($intelipostShipmentId)
    {
        $shipment = $this->shipmentFactory->create();
        $this->resource->load($shipment, $intelipostShipmentId, 'intelipost_shipment_id');
        if (!$shipment->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $intelipostShipmentId));
        }
        return $shipment;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->shipmentCollectionFactory->create();

        $searchResults = $this->searchResultsFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        $items = [];
        /** @var \Intelipost\Shipping\Model\Shipment $model */
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
        ShipmentInterface $shipment
    ) {
        try {
            $shipmentModel = $this->shipmentFactory->create();
            $this->resource->load($shipmentModel, $shipment->getEntityId());
            $this->resource->delete($shipmentModel);
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
