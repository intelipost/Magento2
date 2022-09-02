<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Plugin\Tracking;

use Intelipost\Shipping\Helper\Data;
use Magento\Shipping\Model\InfoFactory;

class Popup
{
    /**
     * @var InfoFactory
     */
    protected $shippingInfoFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param InfoFactory $shippingInfoFactory
     */
    public function __construct(
        InfoFactory $shippingInfoFactory,
        Data $helper
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Shipping\Controller\Tracking\Popup $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute(\Magento\Shipping\Controller\Tracking\Popup $subject, \Closure $proceed)
    {
        try {
            $hash = $subject->getRequest()->getParam('hash');
            $shippingInfoModel = $this->shippingInfoFactory->create()->loadByHash($hash);
            $trackingInfo = $shippingInfoModel->getTrackingInfo();
            if (count($trackingInfo) == 1) {
                $tracking = array_first($trackingInfo);
                if (isset($tracking[0]) && count($tracking) == 1) {
                    if (filter_var($tracking[0]['number'], FILTER_VALIDATE_URL) !== false) {
                        return $subject->getResponse()->setRedirect($tracking[0]['number']);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->getLogger()->error($e->getMessage());
        }

        return $proceed();
    }
}
