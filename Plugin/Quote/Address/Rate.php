<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Plugin\Quote\Address;

class Rate
{
    /**
     * @param $subject
     * @param $proceed
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult $rate
     * @return mixed
     */
    public function aroundImportShippingRate($subject, $proceed, $rate)
    {
        $result = $proceed($rate);

        $warnMessage = $rate->getWarnMessage();
        if (!empty($warnMessage)) {
            $result->setErrorMessage($warnMessage);
        }

        return $result;
    }
}
