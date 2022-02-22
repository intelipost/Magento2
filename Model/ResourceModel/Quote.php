<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Quote extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('intelipost_quotes', 'entity_id');
    }

    public function deleteOldRecords()
    {
        $date = new \DateTime();
        $date->modify('-3 months');
        $dateLimit = $date->format('Y-m-d 00:00:00');

        $this->getConnection()->delete(
            $this->getMainTable(),
            'created_at < "' . $dateLimit . '"'
        );
    }
}
