<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml;

use Intelipost\Shipping\Client\ReadyForShipment;
use Intelipost\Shipping\Client\ShipmentOrder;
use Intelipost\Shipping\Client\Shipped;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory;
use Intelipost\Shipping\Model\Shipment;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

abstract class Shipments extends Action
{
    /** @var Registry */
    protected $coreRegistry;

    /** @var Filter */
    protected $filter;

    /** @var ForwardFactory */
    protected $resultForwardFactory;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var CollectionFactory */
    protected $collectionFactory;

    /** @var Data */
    protected $helper;

    /** @var Shipment */
    protected $shipment;

    /** @var ShipmentOrder */
    protected $shipmentOrder;

    /** @var Shipped */
    protected $shipped;

    /** @var ReadyForShipment */
    protected $readyForShipment;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Shipment $shipment
     * @param ShipmentOrder $shipmentOrder
     * @param Shipped $shipped
     * @param ReadyForShipment $readyForShipment
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Shipment $shipment,
        ShipmentOrder $shipmentOrder,
        Shipped $shipped,
        ReadyForShipment $readyForShipment,
        Data $helper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->filter = $filter;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->shipment = $shipment;
        $this->shipmentOrder = $shipmentOrder;
        $this->helper = $helper;
        $this->shipped = $shipped;
        $this->readyForShipment = $readyForShipment;
        parent::__construct($context);
    }

    /**
     * @param $shipment
     * @param $errorMessages
     * @return void
     */
    public function setError($shipment, $errorMessages)
    {
        $incrementId = $shipment->getData('order_increment_id');
        $message = __('Delivery %1 : %2', $incrementId, $errorMessages);
        $this->messageManager->addErrorMessage($message);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intelipost_Shipping::shipments');
    }

}
