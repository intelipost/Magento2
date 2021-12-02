<?php

namespace Intelipost\Shipping\Model\ResourceModel\Webhook\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * Collection for displaying grid
 */
class Collection extends SearchResult
{
    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable = 'intelipost_webhooks',
        $resourceModel = 'Intelipost\Shipping\Model\ResourceModel\Webhook',
        $identifierName = null,
        $connectionName = null
    )
    {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Webhook\Grid\Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['so' => $this->getConnection()->getTableName('sales_order')],
            'main_table.order_increment_id = so.increment_id',
            ['order_entity_id' => 'entity_id']
        );

        $this->addFilterToMap('order_entity_id', 'so.order_id');

        return $this;
    }
}
