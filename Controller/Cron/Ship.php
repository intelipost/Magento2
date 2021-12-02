<?php

/*
 * @package     Intelipost_Push
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Controller\Cron;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Ship extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    protected $helper;
    protected $collectionFactory;
    protected $_shipment;
    protected $shipped;

    /**
     * @param \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory
     * @param \Intelipost\Shipping\Client\Shipped $shipped
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     * @param \Intelipost\Shipping\Helper\Data $helper
     */
    public function __construct
    (
        \Magento\Backend\App\Action\Context $context,
        \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory,
        \Intelipost\Shipping\Client\Shipped $shipped,
        \Intelipost\Shipping\Model\Shipment $shipment,
        \Intelipost\Shipping\Helper\Data $helper
    )
    {
        parent::__construct($context);
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->_shipment = $shipment;
        $this->shipped = $shipped;
    }

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
        $status = $this->helper->getConfig('status_to_ship', 'order_status', 'intelipost_push');

        if ($enable) {
            /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\Collection $collection */
            $collection = $this->collectionFactory->create();

            $collection->addFieldToFilter('status', ['eq' => $status])
                ->addFieldToFilter('main_table.intelipost_status', ['neq' => \Intelipost\Shipping\Model\Shipment::STATUS_SHIPPED]);

            foreach ($collection as $shipment) {
                /** @var \Intelipost\Shipping\Client\Shipped $col */
                $this->shipped->shippedRequestBody($shipment);
            }
        }
    }
}
