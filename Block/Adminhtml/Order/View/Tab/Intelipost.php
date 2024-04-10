<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Block\Adminhtml\Order\View\Tab;

use Intelipost\Shipping\Model\ResourceModel\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Registry;

class Intelipost extends Template implements TabInterface
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Intelipost_Shipping::order/view/tab/intelipost.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /** @var InvoiceCollectionFactory */
    protected $invoiceCollectionFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param InvoiceCollectionFactory $invoiceCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        InvoiceCollectionFactory $invoiceCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Submit URL getter
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('intelipost/invoices/add');
    }

    /**
     * Remove NFE url
     *
     * @param $invoiceId
     * @return string
     */
    public function getDeleteUrl($invoiceId)
    {
        return $this->getUrl('intelipost/invoices/delete', ['invoice_id' => $invoiceId]);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return __('Intelipost');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return __('Intelipost');
    }

    /**
     * Get Tab Class
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }

    /**
     * Get Class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->getTabClass();
    }

    /**
     * Get Tab Url
     *
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('intelipost/invoices/grid', ['_current' => true]);
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        if (!$this->getOrder()->getIsVirtual()) {
            if ($this->getOrder()->getShippingMethod()) {
                if (strpos($this->getOrder()->getShippingMethod(), 'intelipost') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return mixed
     */
    public function getWebhooksBlock()
    {
        return $this->getLayout()->createBlock(
            \Intelipost\Shipping\Block\Adminhtml\Order\View\Tab\Intelipost\Webhooks::class
        )->setData('order', $this->getOrder())
        ->setData('order_id', $this->getOrderId())
        ->toHtml();
    }

    /**
     * @return mixed
     */
    public function getLabelsBlock()
    {
        return $this->getLayout()->createBlock(
            \Intelipost\Shipping\Block\Adminhtml\Order\View\Tab\Intelipost\Labels::class
        )->setData('order', $this->getOrder())
         ->setData('order_id', $this->getOrderId())
         ->toHtml();
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Invoice\Collection
     */
    public function getInvoicesCollection()
    {
        $invoiceCollection = $this->invoiceCollectionFactory->create();
        $invoiceCollection->addFieldToFilter('order_increment_id', $this->getOrder()->getIncrementId());
        return $invoiceCollection;
    }
}
