<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
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
