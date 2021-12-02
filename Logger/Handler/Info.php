<?php

namespace Intelipost\Shipping\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Info extends Base
{
    protected $fileName = '/var/log/intelipost/info.log';
    protected $loggerType = Logger::INFO;
}
