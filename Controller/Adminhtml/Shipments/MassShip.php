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
        $collectionData = $collection->getData();
        $errorCount = 0;
        $totalCount = 0;
        foreach ($collectionData as $cData) {
            $col = $this->shipped->shippedRequestBody($cData);
            if ($col->getErrorMessages()) {
                $this->messageManager->addErrorMessage('Entrega ' . $cData['order_increment_id'] . "</br>" . $col->getErrorMessages());
                $errorCount++;
            }
            $totalCount++;
        }
        $successCount = $totalCount - $errorCount;

        if ($successCount == 1) {
            $this->messageManager->addSuccessMessage('Entrega despachada com sucesso: 1.');
        }

        if ($successCount > 1) {
            $this->messageManager->addSuccessMessage('Entregas despachadas com sucesso: ' . $successCount . '.');
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
