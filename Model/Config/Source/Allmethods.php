<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
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
