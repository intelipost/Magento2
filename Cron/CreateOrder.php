<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Client\ShipmentOrder;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Intelipost\Shipping\Model\Shipment;

class CreateOrder
{
    /** @var Data  */
    protected $helper;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var ShipmentOrder  */
    protected $shipmentOrder;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ShipmentOrder $shipmentOrder
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ShipmentOrder $shipmentOrder,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->shipmentOrder = $shipmentOrder;
        $this->helper = $helper;
    }

    public function execute()
    {
        $enable = $this->helper->getConfig('enable_cron', 'order_status', 'intelipost_push');
        $byShipment = (boolean) $this->helper->getConfig('order_by_shipment', 'order_status', 'intelipost_push');
        $status = $this->helper->getConfig('status_to_create', 'order_status', 'intelipost_push');

        if ($enable) {
            $statuses = explode(',', $status);

            $collection = $this->collectionFactory->create();
            if ($byShipment) {
                $collection->getSelect()
                    ->join(
                        ['so' => $collection->getConnection()->getTableName('sales_order')],
                        'main_table.order_increment_id = so.increment_id',
                        ['increment_id']
                    )
                    ->join(
                        ['ss' => $collection->getConnection()->getTableName('sales_shipment')],
                        'so.entity_id = ss.order_id',
                        ['increment_id AS shipment_increment_id']
                    )
                ;
            } else {
                $collection->getSelect()->join(
                    ['so' => $collection->getConnection()->getTableName('sales_order')],
                    'main_table.order_increment_id = so.increment_id',
                    ['increment_id']
                );
            }
            $collection
                ->addFieldToFilter('status', ['in' => $statuses])
                ->addFieldToFilter('main_table.intelipost_status', Shipment::STATUS_PENDING);

            foreach ($collection as $shipment) {
                try {
                    $this->shipmentOrder->execute($shipment);
                } catch (\Exception $e) {
                    $this->helper->log($e->getMessage());
                }
            }
        }
    }
}
