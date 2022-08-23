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

    /** @var Shipment  */
    protected $_shipment;

    /** @var Shipped  */
    protected $shipped;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Shipped $shipped
     * @param Shipment $shipment
     * @param Data $helper
     */
    public function __construct
    (
        CollectionFactory $collectionFactory,
        Shipped $shipped,
        Shipment $shipment,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->_shipment = $shipment;
        $this->shipped = $shipped;
    }

    public function execute()
    {
        $enable = $this->helper->getConfig('enable_cron', 'order_status', 'intelipost_push');
        $status = $this->helper->getConfig('status_to_ship', 'order_status', 'intelipost_push');

        if ($enable) {
            /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->getSelect()->joinLeft(
                ['so' => $collection->getConnection()->getTableName('sales_order')],
                'main_table.order_increment_id = so.increment_id',
                ['increment_id']
            );

            $collection->addFieldToFilter('status', ['eq' => $status])
                ->addFieldToFilter(
                    'main_table.intelipost_status',
                    ['neq' => Shipment::STATUS_SHIPPED]
                );

            foreach ($collection as $shipment) {
                /** @var Shipped $col */
                $this->shipped->shippedRequestBody($shipment);
            }
        }
    }
}
