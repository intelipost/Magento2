<?php

/**
 *
 * @package Intelipost\Shipping
 * @author Thiago Contardi
 * @copyright Copyright (c) 2019 Bizcommerce (based on Imagination media module )
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source;

class DimensionType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'cm', 'label' => __('Centimetre')],
            ['value' => 'm', 'label' => __('Metre')],
        ];
    }
}
