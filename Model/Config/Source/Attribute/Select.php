<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
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
