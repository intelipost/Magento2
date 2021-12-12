<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source;

class Allmethods implements \Magento\Framework\Option\ArrayInterface
{
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
