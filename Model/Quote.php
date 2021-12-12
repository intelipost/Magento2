<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\QuoteInterface;
use Intelipost\Shipping\Model\ResourceModel\Quote as ResourceQuote;
use Magento\Framework\Model\AbstractModel;


class Quote extends AbstractModel implements QuoteInterface
{
    /**
     * Unique identifier to be used in caching
     * @var string
     */
    protected $_cacheTag = 'intelipost_quotes';

    /**
     * Prefix for triggered events
     * @var string
     */
    protected $_eventPrefix = 'intelipost_quotes';

    protected function _construct()
    {
        $this->_init(ResourceQuote::class);
    }
}
