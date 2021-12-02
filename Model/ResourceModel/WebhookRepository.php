<?php
/**
 * @package Biz
 * @author Thiago Contardi
 * @copyright Copyright (c) 2020 Bizcommerce
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Intelipost\Shipping\Api\Data\WebhookInterface;
use Intelipost\Shipping\Model\WebhookFactory;
use Intelipost\Shipping\Api\Data\WebhookSearchResultsInterfaceFactory;
use Intelipost\Shipping\Api\WebhookRepositoryInterface;
use Intelipost\Shipping\Model\ResourceModel\Webhook as ResourceWebhook;
use Intelipost\Shipping\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class WebhookRepository implements WebhookRepositoryInterface
{
    /** @var ResourceWebhook  */
    protected $resource;

    /** @var WebhookFactory  */
    protected $webhookFactory;

    /** @var WebhookCollectionFactory  */
    protected $webhookCollectionFactory;

    /** @var WebhookSearchResultsInterfaceFactory  */
    protected $searchResultsFactory;

    /** @var JoinProcessorInterface  */
    protected $extensionAttributesJoinProcessor;

    /** @var CollectionProcessorInterface  */
    protected $collectionProcessor;

    /**
     * @param ResourceWebhook $resource
     * @param WebhookFactory $webhookFactory
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param WebhookSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceWebhook $resource,
        WebhookFactory $webhookFactory,
        WebhookCollectionFactory $webhookCollectionFactory,
        WebhookSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    )
    {
        $this->resource = $resource;
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->webhookFactory = $webhookFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        WebhookInterface $webhook
    ) {
        try {
            $webhook = $this->resource->save($webhook);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Item: %1',
                $exception->getMessage()
            ));
        }
        return $webhook;
    }

    /**
     * {@inheritdoc}
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $webhook = $this->webhookFactory->create();
        $this->resource->load($webhook, $id);
        if (!$webhook->getId()) {
            throw new NoSuchEntityException(__('Item with id "%1" does not exist.', $id));
        }
        return $webhook;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    )
    {
        $collection = $this->webhookCollectionFactory->create();

        $searchResults = $this->searchResultsFactory->create();

        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
            $searchResults->setSearchCriteria($searchCriteria);
        }

        $items = [];
        /** @var \Intelipost\Shipping\Model\Webhook $model */
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
        WebhookInterface $webhook
    )
    {
        try {
            $webhookModel = $this->webhookFactory->create();
            $this->resource->load($webhookModel, $webhook->getEntityId());
            $this->resource->delete($webhookModel);
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


