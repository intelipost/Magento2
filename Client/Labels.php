<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Api\ShipmentRepositoryInterface;
use Intelipost\Shipping\Helper\Api;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\Label;
use Intelipost\Shipping\Model\Shipment;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Intelipost\Shipping\Model\ResourceModel\LabelRepository;
use Intelipost\Shipping\Model\LabelFactory;

class Labels extends AbstractShipment
{
    /** @var LabelRepository */
    protected $labelRepository;

    /** @var LabelFactory */
    protected $labelFactory;
    /**
     * @param Api $helperApi
     * @param Data $helper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TimezoneInterface $timezone
     * @param LabelRepository $labelRepository
     * @param LabelFactory $labelFactory
     */
    public function __construct(
        Api $helperApi,
        Data $helper,
        ShipmentRepositoryInterface $shipmentRepository,
        TimezoneInterface $timezone,
        LabelRepository $labelRepository,
        LabelFactory $labelFactory
    ) {
        $this->labelFactory = $labelFactory;
        $this->labelRepository = $labelRepository;
        parent::__construct($helperApi, $helper, $shipmentRepository, $timezone);
    }

    /**
     * @param string $orderIncrementId
     * @return array
     * @throws \Exception
     */
    public function getVolumes(string $orderIncrementId)
    {
        try {
            $requestPath = sprintf('shipment_order/%s', $orderIncrementId);
            $response = $this->helperApi->apiRequest('GET', $requestPath);
            $result = $this->helper->unserializeData($response);

            if ($result['status'] == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_OK) {
                if (isset($result['content']) && isset($result['content']['shipment_order_volume_array'])) {
                    return $result['content']['shipment_order_volume_array'];
                }
            }
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }
        return [];
    }

    public function importPrintingLabels($incrementId, $volumeNumber)
    {
        $path = sprintf('shipment_order/get_label/%s/%s', $incrementId, $volumeNumber);
        $response = $this->helperApi->apiRequest('GET', $path);
        $result = $this->helper->unserializeData($response);
        $responseStatus = $result['status'];
        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_OK) {
            $labelUrl = $result['content']['label_url'];

            $labelModel = $this->labelFactory->create();
            $labelModel->setOrderIncrementId($incrementId);
            $labelModel->setUrl($labelUrl);
            $this->labelRepository->save($labelModel);
        }
    }
}
