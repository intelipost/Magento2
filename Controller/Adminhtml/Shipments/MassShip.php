<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
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
            /** @var \Intelipost\Shipping\Client\Shipped $shipped */
            $shipped = $this->shipped->shippedRequestBody($shipment);
            $incrementId = $shipment->getData('order_increment_id');
            if (!$shipped->getErrorMessages()) {
                $this->helper->createOrderShipment($incrementId, $shipment->getData('tracking_url'));
            } else {
                $this->messageManager->addErrorMessage(__('Delivery %1 : %2', $incrementId, $shipped->getErrorMessages()));
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
