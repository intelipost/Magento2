<?php
/*
 * @package     Intelipost_Push
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Model\ResourceModel\Invoice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Intelipost\Shipping\Model\Invoice as InvoiceModel;
use Intelipost\Shipping\Model\ResourceModel\Invoice as InvoiceResource;

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
    protected $_eventPrefix = 'intelipost_invoices_collection';

    /**
     * Object name to access in events
     * @var string
     */
    protected $_eventObject = 'intelipost_invoices_collection';

    protected function _construct()
    {
        $this->_init(
            InvoiceModel::class,
            InvoiceResource::class
        );
    }

}
