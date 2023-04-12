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

class RetryOrder
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
                $cond = 'main_table.intelipost_shipment_id LIKE CONCAT(\'%\', so.increment_id, \'%\')';
            } else {
                $cond = 'main_table.order_increment_id = so.increment_id';
            }
            $collection->getSelect()->joinLeft(
                ['so' => $collection->getConnection()->getTableName('sales_order')],
                $cond,
                ['increment_id']
            );
            $collection
                ->addFieldToFilter('status', ['in' => $statuses])
                ->addFieldToFilter('main_table.intelipost_status', Shipment::STATUS_ERROR);

            foreach ($collection as $shipment) {
                $this->shipmentOrder->execute($shipment);
            }
        }
    }
}
