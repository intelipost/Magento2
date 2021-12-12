<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

class ShipOrder
{
    protected $helper;
    protected $collectionFactory;
    protected $_shipment;
    protected $shipped;

    public function __construct
    (
        \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory,
        \Intelipost\Shipping\Client\Shipped $shipped,
        \Intelipost\Shipping\Model\Shipment $shipment,
        \Intelipost\Shipping\Helper\Data $helper
    )
    {
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

            $collection->addFieldToFilter('status', ['eq' => $status])
                ->addFieldToFilter(
                    'main_table.intelipost_status',
                    ['neq' => \Intelipost\Shipping\Model\Shipment::STATUS_SHIPPED]
                );

            foreach ($collection as $shipment) {
                /** @var \Intelipost\Shipping\Client\Shipped $col */
                $this->shipped->shippedRequestBody($shipment);
            }
        }
    }
}
