<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Unit implements OptionSourceInterface
{

    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'gr', 'label' => __('Gram')],
            ['value' => 'kg', 'label' => __('Kilo')]
        ];
    }
}
