<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model\Config\Source\Attribute;

class Select extends \Intelipost\Shipping\Model\Config\Source\Attributes
{
    public function toOptionArray()
    {
        $result = [
            ['value' => '', 'label' => __(' --- Please Select --- ')]
        ];

        return array_merge($result, parent::toOptionArray());
    }
}
