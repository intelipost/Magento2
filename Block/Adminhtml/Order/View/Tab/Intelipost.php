<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Block\Adminhtml\Order\View\Tab;

use Intelipost\Shipping\Model\ResourceModel\Invoice\CollectionFactory;
use Intelipost\Shipping\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;

class Intelipost extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /** @var CollectionFactory */
    protected $invoiceCollectionFactory;

    /** @var WebhookCollectionFactory */
    protected $webhookCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $invoiceCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $invoiceCollectionFactory,
        WebhookCollectionFactory $webhookCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
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
        if ($this->_authorization->isAllowed('Intelipost_Shipping::webhooks')) {
            if (strpos($this->getOrder()->getShippingMethod(), 'intelipost') !== false) {
                return true;
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
     * @return \Intelipost\Shipping\Model\ResourceModel\Invoice\Collection
     */
    public function getInvoicesCollection()
    {
        $invoiceCollection = $this->invoiceCollectionFactory->create();
        $invoiceCollection->addFieldToFilter('order_increment_id', $this->getOrder()->getIncrementId());
        return $invoiceCollection;
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Webhook\Collection
     */
    public function getWebhooksCollection()
    {
        $collection = $this->webhookCollectionFactory->create();
        $collection->addFieldToFilter('order_increment_id', $this->getOrder()->getIncrementId());
        return $collection;
    }
}
