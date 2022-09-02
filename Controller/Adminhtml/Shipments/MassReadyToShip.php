<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Shipments;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassReadyToShip extends \Intelipost\Shipping\Controller\Adminhtml\Shipments
{
    protected $redirectUrl = 'intelipost/shipments/index';

    /**
     * @return ResponseInterface|Redirect|Redirect&ResultInterface|ResultInterface|ResultInterface&Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath($this->redirectUrl);
        }

        return $resultRedirect;
    }

    /**
     * @param AbstractCollection $collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function massAction(AbstractCollection $collection)
    {
        $errorCount = 0;
        $totalCount = 0;
        foreach ($collection as $shipment) {
            try {
                /** @var \Intelipost\Shipping\Client\ReadyForShipment $response */
                $response = $this->readyForShipment->readyForShipmentRequestBody($shipment);
                if ($response->getErrorMessages()) {
                    $this->setError($shipment, $response->getErrorMessages());
                    $errorCount++;
                }
                $totalCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $this->helper->log($e->getMessage());
            }
        }
        $successCount = $totalCount - $errorCount;

        if ($successCount > 0) {
            $this->messageManager->addSuccessMessage(__('%1 shipments sent', $successCount));
        }
    }

    /**
     * @return mixed|string
     */
    protected function getComponentRefererUrl()
    {
        return $this->filter->getComponentRefererUrl() ?: $this->redirectUrl;
    }
}
