<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Client\Shipped;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Intelipost\Shipping\Model\Shipment;

class ShipOrder
{
    /** @var Data  */
    protected $helper;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var Shipped  */
    protected $shipped;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Shipped $shipped
     * @param Data $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Shipped $shipped,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->shipped = $shipped;
    }

    public function execute()
    {
        $enable = $this->helper->getConfig('enable_cron', 'order_status', 'intelipost_push');
        $byShipment = (boolean) $this->helper->getConfig('order_by_shipment', 'order_status', 'intelipost_push');
        $status = (string) $this->helper->getConfig('status_to_shipped', 'order_status', 'intelipost_push');

        if ($enable && $status) {
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
            $collection->addFieldToFilter('status', ['eq' => $status])
                ->addFieldToFilter(
                    'main_table.intelipost_status',
                    ['neq' => Shipment::STATUS_SHIPPED]
                );

            foreach ($collection as $shipment) {
                try {
                    $this->shipped->shippedRequestBody($shipment);
                } catch (\Exception $e) {
                    $this->helper->log($e->getMessage());
                }
            }
        }
    }
}
