<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Client\Labels as RequestLabel;
use Intelipost\Shipping\Client\ShipmentOrder;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Intelipost\Shipping\Model\Shipment;

class ImportLabels
{
    /** @var Data  */
    protected $helper;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var ShipmentOrder  */
    protected $shipmentOrder;

    /** @var RequestLabel  */
    protected $requestLabel;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ShipmentOrder $shipmentOrder
     * @param RequestLabel $requestLabel
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ShipmentOrder $shipmentOrder,
        RequestLabel $requestLabel,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->shipmentOrder = $shipmentOrder;
        $this->requestLabel = $requestLabel;
        $this->helper = $helper;
    }

    public function execute()
    {
        $enable = $this->helper->getConfig('enable_cron', 'order_status', 'intelipost_push');
        $byShipment = (boolean) $this->helper->getConfig('order_by_shipment', 'order_status', 'intelipost_push');

        if ($enable) {
            try {
                $statuses = [
                    Shipment::STATUS_PENDING,
                    Shipment::STATUS_ERROR
                ];

                $collection = $this->collectionFactory->create();
                if ($byShipment) {
                    $cond = 'main_table.intelipost_shipment_id LIKE CONCAT(\'%\', so.increment_id, \'%\')';
                } else {
                    $cond = 'main_table.order_increment_id = so.increment_id';
                }
                $collection->getSelect()->join(
                    ['so' => $collection->getConnection()->getTableName('sales_order')],
                    $cond,
                    ['increment_id']
                );
                $collection->getSelect()->joinLeft(
                    ['il' => $collection->getConnection()->getTableName('intelipost_labels')],
                    'il.order_increment_id = so.increment_id',
                    ['url']
                );
                $collection->addFieldToFilter('main_table.intelipost_status', ['nin' => $statuses]);
                $collection->addFieldToFilter('il.url', ['null' => true]);
                $collection = $this->filterRecentDays($collection);

                foreach ($collection as $order) {
                    try {
                        $incrementId = $order->getData('increment_id');
                        $volumes = $this->requestLabel->getVolumes($incrementId);
                        if (!empty($volumes)) {
                            foreach ($volumes as $volume) {
                                $this->requestLabel->importPrintingLabels(
                                    $incrementId,
                                    $volume['shipment_order_volume_number']
                                );
                            }
                        }
                    } catch (\Exception $e) {
                        $this->helper->log($e->getMessage());
                    }
                }

            } catch (\Exception $e) {
                $this->helper->log($e->getMessage());
            }
        }
    }

    /**
     * @param CollectionFactory $collection
     * @return CollectionFactory
     */
    public function filterRecentDays($collection)
    {
        $date = new \DateTime();
        $date->modify('-30 days');
        $formattedDate = $date->format('Y-m-d');
        $collection->addFieldToFilter('so.created_at', ['gteq' => $formattedDate]);
        return $collection;
    }
}
