<?php

/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Client\ShipmentOrder;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;

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
    )
    {
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
            $collection
                ->addFieldToFilter('status', ['in' => $statuses])
                ->addFieldToFilter('main_table.intelipost_status', 'error');

            foreach ($collection as $shipment) {
                $this->shipmentOrder->execute($shipment);
            }
        }
    }
}
