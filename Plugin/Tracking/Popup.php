<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Plugin\Tracking;

use Intelipost\Shipping\Helper\Data;
use Magento\Shipping\Model\InfoFactory;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;

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

            $trackingNumber = $this->getTrackingNumber($trackingInfo);
            if ($trackingNumber) {
                if (filter_var($trackingNumber, FILTER_VALIDATE_URL) !== false) {
                    $subject->getResponse()->setRedirect($trackingNumber);
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->helper->getLogger()->error($e->getMessage());
        }

        $proceed();
    }

    protected function getTrackingNumber($trackingInfo)
    {
        $trackingNumber = '';
        if (is_array($trackingInfo)) {
            foreach ($trackingInfo as $tracking) {
                if (isset($tracking[0])) {
                    $this->helper->getLogger()->info(json_encode($tracking));
                    $trackingItem = $tracking[0];
                    if (is_array($trackingItem)) {
                        return $trackingItem['number'];
                    }
                }
            }
        }
        return $trackingNumber;
    }
}
