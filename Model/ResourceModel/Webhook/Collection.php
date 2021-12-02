<?php
/*
 * @package     Intelipost_Push
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Model\ResourceModel\Webhook;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Intelipost\Shipping\Model\Webhook as WebhookModel;
use Intelipost\Shipping\Model\ResourceModel\Webhook as WebhookResource;

class Collection extends AbstractCollection
{

    /**
     * Field name for entity id
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Prefix for triggered events
     * @var string
     */
    protected $_eventPrefix = 'intelipost_webhooks_collection';

    /**
     * Object name to access in events
     * @var string
     */
    protected $_eventObject = 'intelipost_webhooks_collection';

    protected function _construct()
    {
        $this->_init(
            WebhookModel::class,
            WebhookResource::class
        );
    }

}
