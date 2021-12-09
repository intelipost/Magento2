<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Shipments;

use Magento\Framework\Controller\ResultFactory;

class MassShip extends \Intelipost\Shipping\Controller\Adminhtml\Shipments
{
    protected $redirectUrl = 'intelipost/shipments/index';

    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection)
    {
        $errorCount = 0;
        $totalCount = 0;
        foreach ($collection as $shipment) {
            $col = $this->shipped->shippedRequestBody($shipment);
            if ($col->getErrorMessages()) {
                $incrementId = $shipment->getData('order_increment_id');
                $this->messageManager->addErrorMessage(__('Delivery %1 : %2', $incrementId, $col->getErrorMessages()));
                $errorCount++;
            }
            $totalCount++;
        }
        $successCount = $totalCount - $errorCount;

        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(__('%1 shipments sent', $successCount ));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }
}
