<?php

namespace Intelipost\Shipping\Plugin\Quote\Cart;

class ShippingMethodConverter
{
    /**
     * @param $subject
     * @param $proceed
     * @param \Magento\Quote\Model\Quote\Address\Rate $rateModel
     * @param $quoteCurrencyCode
     * @return mixed
     */
    public function aroundModelToDataObject($subject, $proceed, $rateModel, $quoteCurrencyCode)
    {
        $result = $proceed($rateModel, $quoteCurrencyCode);

        $warnMessage = $rateModel->getWarnMessage();
        if (!empty($warnMessage)) {
            $result->setErrorMessage($warnMessage);
        }

        return $result;
    }
}
