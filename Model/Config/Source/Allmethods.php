<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Allmethods implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'none',
                'label' => __('None')
            ],
            [
                'value' => 'lower_price',
                'label' => __('Lower Price'),
            ],
            [
                'value' => 'lower_delivery_date',
                'label' => __('Lower Delivery Date')
            ],
        ];
    }
}
