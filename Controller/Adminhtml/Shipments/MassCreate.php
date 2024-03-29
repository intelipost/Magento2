<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Shipments;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassCreate extends \Intelipost\Shipping\Controller\Adminhtml\Shipments
{
    protected $redirectUrl = 'intelipost/shipments/index';

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        try {
            /** @var \Intelipost\Shipping\Model\ResourceModel\Shipment\Collection $collection */
            $shipmentCollection = $this->collectionFactory->create();
            $collection = $this->filter->getCollection($shipmentCollection);
            $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath($this->redirectUrl);
        }
        return $resultRedirect;
    }

    /**
     * @param AbstractCollection $collection
     */
    protected function massAction(AbstractCollection $collection)
    {
        $errorCount = 0;
        $totalCount = 0;

        foreach ($collection as $shipment) {
            $item = $this->shipmentOrder->execute($shipment);
            if ($item->getErrorMessages()) {
                $orderId = $shipment->getData('order_increment_id');
                $errorMessage = $item->getErrorMessages();
                $this->messageManager->addErrorMessage(__('Shipment %1 - %2', $orderId, $errorMessage));
                $errorCount++;
            }
            $totalCount++;
        }

        $successCount = $totalCount - $errorCount;
        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(__('Shipments successfully created'));
        }
    }

    /**
     * @return string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }
}
