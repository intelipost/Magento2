<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('intelipost_quotes', 'entity_id');
    }
}
