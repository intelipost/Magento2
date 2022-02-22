<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Cron;

use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Quote as ResourceQuote;

class ClearQuotes
{
    /** @var Data  */
    protected $helper;

    /** @var ResourceQuote  */
    protected $resourceQuote;

    /**
     * @param ResourceQuote $resourceQuote
     * @param Data $helper
     */
    public function __construct(
        ResourceQuote $resourceQuote,
        Data $helper
    )
    {
        $this->resourceQuote = $resourceQuote;
        $this->helper = $helper;
    }

    public function execute()
    {
        try {
            $this->resourceQuote->deleteOldRecords();
        } catch (\Exception $e) {
            $this->helper->getLogger()->error($e->getMessage());
        }
    }
}
