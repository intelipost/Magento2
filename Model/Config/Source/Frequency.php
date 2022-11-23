<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Intelipost\Shipping\Model\Config\Source;

class Frequency implements \Magento\Framework\Data\OptionSourceInterface
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
                ['label' => __('Each 5 minutes'), 'value' => '*/5 * * * *'],
                ['label' => __('Each 15 minutes'), 'value' => '*/15 * * * *'],
                ['label' => __('Hourly'), 'value' => '45 * * * *'],
                ['label' => __('Each 2 hours'), 'value' => '45 */2 * * *'],
            ];
        }
        return self::$options;
    }
}
