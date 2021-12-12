<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Shipments;

class Index extends \Intelipost\Shipping\Controller\Adminhtml\Shipments
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intelipost_Shipping::shipments');
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Intelipost_Shipping::shipments');
        $resultPage->getConfig()->getTitle()->prepend(__('Order Shipments'));
        $resultPage->addBreadcrumb(__('Push'), __('Manage Orders'));

        return $resultPage;
    }

}
