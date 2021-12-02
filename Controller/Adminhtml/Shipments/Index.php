<?php
/*
 * @package     Intelipost_Pickup
 * @copyright   Copyright (c) 2016 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author      Eneias Ramos de Melo <eneias@gamuza.com.br>
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
