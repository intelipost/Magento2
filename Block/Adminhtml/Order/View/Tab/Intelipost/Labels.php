<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Block\Adminhtml\Order\View\Tab\Intelipost;

use Intelipost\Shipping\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Intelipost\Shipping\Model\ResourceModel\ShipmentRepository;
use Intelipost\Shipping\Model\ShipmentFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Labels extends \Magento\Backend\Block\Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Intelipost_Shipping::order/view/tab/intelipost/labels.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /** @var LabelCollectionFactory */
    protected $labelsCollectionFactory;

    /** @var ShipmentRepository */
    protected $shipmentRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param ShipmentRepository $shipmentRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LabelCollectionFactory $labelCollectionFactory,
        ShipmentRepository $shipmentRepository,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->labelsCollectionFactory = $labelCollectionFactory;
        $this->shipmentRepository = $shipmentRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Label\Collection
     */
    public function hasOrderCreated()
    {
        $shipment = $this->shipmentRepository->getByOrderIncrementId($this->getOrder()->getIncrementId());
        return $shipment->getId() ? true : false;
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Label\Collection
     */
    public function getLabelsCollection()
    {
        $collection = $this->labelsCollectionFactory->create();
        $collection->addFieldToFilter('order_increment_id', $this->getOrder()->getIncrementId());
        return $collection;
    }
}
