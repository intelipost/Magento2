<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Info extends Base
{
    protected $fileName = '/var/log/intelipost/info.log';
    protected $loggerType = Logger::INFO;
}
