<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Shipments;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassCreate extends \Intelipost\Shipping\Controller\Adminhtml\Shipments
{
    protected $redirectUrl = 'intelipost/shipments/index';

    public function execute()
    {
        try {
            /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\Collection $collection */
            $shipmentCollection = $this->collectionFactory->create();
            $collection = $this->filter->getCollection($shipmentCollection);
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $errorCount = 0;
        $totalCount = 0;

        foreach ($collection as $shipment) {
            $col = $this->shipmentOrder->execute($shipment);
            if ($col->getErrorMessages()) {
                $this->messageManager->addErrorMessage(__('Shipment %1 - %2', $shipment->getData('order_increment_id'), $col->getErrorMessages()));
                $errorCount++;
            }
            $totalCount++;
        }

        $successCount = $totalCount - $errorCount;
        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(__('Shipments successfully created'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * @return string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }
}
