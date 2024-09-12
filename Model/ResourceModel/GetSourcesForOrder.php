<?php

declare(strict_types=1);

namespace Intelipost\Shipping\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Get allocated sources for specified order.
 */
class GetSourcesForOrder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get allocated sources by order ID
     *
     * @param int $orderId
     * @return array
     */
    public function execute(int $orderId): array
    {
        $sources = [];
        $shipmentsIds = $this->getShipmentIds($orderId);

        /** Get sources for shipment ids */
        if (!empty($shipmentsIds)) {
            $connection = $this->resourceConnection->getConnection();
            $sourceTableName = $this->resourceConnection->getTableName('inventory_source');
            $shipmentSourceTableName = $this->resourceConnection->getTableName('inventory_shipment_source');

            $select = $connection->select()
                ->from(['inventory_source' => $sourceTableName])
                ->joinInner(
                    ['shipment_source' => $shipmentSourceTableName],
                    'shipment_source.source_code = inventory_source.source_code',
                    []
                )
                ->group('inventory_source.source_code')
                ->where('shipment_source.shipment_id in (?)', $shipmentsIds);

            $sources = $connection->fetchRow($select);
        }

        return $sources;
    }

    /**
     * @param int $orderId
     * @return mixed
     */
    public function getShipmentIds(int $orderId): mixed
    {
        $salesConnection = $this->resourceConnection->getConnection('sales');
        $shipmentTableName = $this->resourceConnection->getTableName('sales_shipment', 'sales');
        /** Get shipment ids for order */
        $shipmentSelect = $salesConnection->select()
            ->from(
                ['sales_shipment' => $shipmentTableName],
                ['shipment_id' => 'sales_shipment.entity_id']
            )
            ->where('sales_shipment.order_id = ?', $orderId);
        return $salesConnection->fetchCol($shipmentSelect);
    }
}
