<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Client\ReadyForShipment;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Intelipost\Shipping\Model\Shipment;

class ReadyForShipmentOrder
{
    /** @var Data  */
    protected $helper;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var Shipment  */
    protected $shipment;

    /** @var ReadyForShipment  */
    protected $readyForShipment;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ReadyForShipment $readyForShipment
     * @param Shipment $shipment
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ReadyForShipment $readyForShipment,
        Shipment $shipment,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->shipment = $shipment;
        $this->readyForShipment = $readyForShipment;
    }

    public function execute()
    {
        $enable = $this->helper->getConfig('enable_cron', 'order_status', 'intelipost_push');
        $byShipment = (boolean) $this->helper->getConfig('order_by_shipment', 'order_status', 'intelipost_push');
        $status = $this->helper->getConfig('status_to_ready_to_ship', 'order_status', 'intelipost_push');

        if ($enable) {
            $statuses = explode(',', $status);
            /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\Collection $collection */
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
            $collection->addFieldToFilter('status', ['in' => $statuses])
                ->addFieldToFilter(
                    'main_table.intelipost_status',
                    ['neq' => Shipment::STATUS_READY_FOR_SHIPMENT]
                );

            foreach ($collection as $shipment) {
                try {
                    /** @var ReadyForShipment $shipment */
                    $this->readyForShipment->readyForShipmentRequestBody($shipment);
                } catch (\Exception $e) {
                    $this->helper->log($e->getMessage());
                }
            }
        }
    }
}
