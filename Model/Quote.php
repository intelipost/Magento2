<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
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
