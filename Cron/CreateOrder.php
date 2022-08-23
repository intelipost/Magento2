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
        $status = $this->helper->getConfig('status_to_create', 'order_status', 'intelipost_push');

        if ($enable) {
            $statuses = explode(',', $status);

            $collection = $this->collectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['so' => $collection->getConnection()->getTableName('sales_order')],
                'main_table.order_increment_id = so.increment_id',
                ['increment_id']
            );
            $collection
                ->addFieldToFilter('status', ['in' => $statuses])
                ->addFieldToFilter('main_table.intelipost_status', 'pending');

            foreach ($collection as $shipment) {
                $this->shipmentOrder->execute($shipment);
            }
        }
    }
}
