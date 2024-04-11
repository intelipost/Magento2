<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Block\Adminhtml\Order\View\Tab\Intelipost;

use Intelipost\Shipping\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Webhooks extends Template
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'Intelipost_Shipping::order/view/tab/intelipost/webhooks.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /** @var WebhookCollectionFactory */
    protected $webhookCollectionFactory;


    /**
     * @param Context $context
     * @param Registry $registry
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        WebhookCollectionFactory $webhookCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Intelipost\Shipping\Model\ResourceModel\Webhook\Collection
     */
    public function getWebhooksCollection()
    {
        $collection = $this->webhookCollectionFactory->create();
        $collection->addFieldToFilter('order_increment_id', $this->getData('order')->getIncrementId());
        return $collection;
    }
}
