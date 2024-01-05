<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Intelipost\Shipping\Model\Config\Source;

class Street implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     */
    protected static $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!self::$options) {
            self::$options = [
                ['label' => __('Street 1'), 'value' => '1'],
                ['label' => __('Street 2'), 'value' => '2'],
                ['label' => __('Street 3'), 'value' => '3'],
                ['label' => __('Street 4'), 'value' => '4']
            ];
        }
        return self::$options;
    }
}
