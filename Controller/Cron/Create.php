<?php

/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Controller\Cron;

use Intelipost\Shipping\Client\ShipmentOrder;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Create extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /** @var Data  */
    protected $helper;

    /** @var CollectionFactory  */
    protected $collectionFactory;

    /** @var ShipmentOrder  */
    protected $shipmentOrder;

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        ShipmentOrder $shipmentOrder,
        Data $helper
    )
    {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->shipmentOrder = $shipmentOrder;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
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
                ->addFieldToFilter('main_table.intelipost_status', 'pending');

            foreach ($collection as $shipment) {
                /** @var \Intelipost\Shipping\Client\ShipmentOrder $col */
                $this->shipmentOrder->execute($shipment);
            }
        }
    }
}
