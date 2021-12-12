<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml;

abstract class Shipments extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\Registry */
    protected $coreRegistry;

    /** @var \Magento\Ui\Component\MassAction\Filter */
    protected $filter;

    /** @var \Magento\Backend\Model\View\Result\ForwardFactory */
    protected $resultForwardFactory;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory */
    protected $collectionFactory;

    /** @var \Intelipost\Shipping\Helper\Data */
    protected $helper;

    /** @var \Intelipost\Shipping\Model\Shipment */
    protected $shipment;

    /** @var \Intelipost\Shipping\Client\ShipmentOrder */
    protected $shipmentOrder;

    /** @var \Intelipost\Shipping\Client\Shipped */
    protected $shipped;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     * @param \Intelipost\Shipping\Client\ShipmentOrder $shipmentOrder
     * @param \Intelipost\Shipping\Client\Shipped $shipped
     * @param \Intelipost\Shipping\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Intelipost\Shipping\Model\ResourceModel\Shipment\CollectionFactory $collectionFactory,
        \Intelipost\Shipping\Model\Shipment $shipment,
        \Intelipost\Shipping\Client\ShipmentOrder $shipmentOrder,
        \Intelipost\Shipping\Client\Shipped $shipped,
        \Intelipost\Shipping\Helper\Data $helper
    )
    {
        $this->coreRegistry = $coreRegistry;
        $this->filter = $filter;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->shipment = $shipment;
        $this->shipmentOrder = $shipmentOrder;
        $this->helper = $helper;
        $this->shipped = $shipped;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intelipost_Shipping::shipments');
    }

}
