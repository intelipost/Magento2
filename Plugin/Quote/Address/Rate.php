<?php

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
