<?php

namespace Intelipost\Shipping\Model\Config\Source\Order;
/**
 * Class Status
 * @api
 * @since 100.0.2
 */
class ProcessingComplete extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        \Magento\Sales\Model\Order::STATE_NEW,
        \Magento\Sales\Model\Order::STATE_PROCESSING,
        \Magento\Sales\Model\Order::STATE_COMPLETE
    ];

}
