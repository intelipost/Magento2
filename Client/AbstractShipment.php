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
use Intelipost\Shipping\Model\Shipment;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class AbstractShipment
{
    /** @var string */
    protected $message;

    /** @var Data  */
    protected $helper;

    /** @var Api  */
    protected $helperApi;

    /** @var TimezoneInterface  */
    protected $timezone;

    /** @var ShipmentRepositoryInterface  */
    protected $shipmentRepository;

    /**
     * @param Api $helperApi
     * @param Data $helper
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Api $helperApi,
        Data $helper,
        ShipmentRepositoryInterface $shipmentRepository,
        TimezoneInterface $timezone
    ) {
        $this->helper = $helper;
        $this->helperApi = $helperApi;
        $this->timezone = $timezone;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param $shipment
     * @return \stdClass[]
     */
    public function prepareRequestBody($shipment)
    {
        $byShipment = (boolean) $this->helper->getConfig('order_by_shipment', 'order_status', 'intelipost_push');

        $date = $this->timezone->date();
        $eventDate = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $body = new \stdClass();
        if (!$byShipment) {
            $body->order_number = $shipment->getData('order_increment_id');
        } else {
            $body->order_number = $shipment->getData('intelipost_shipment_id')
                ?: $shipment->getData('order_increment_id');
        }
        $body->event_date  = str_replace(' ', 'T', $eventDate);

        return [$body];
    }

    /**getConfig
     * @param $shipment
     * @param array $result
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setError($shipment, $result)
    {
        $messages = null;
        $errorCount = 1;
        foreach ($result['messages'] as $msg) {
            $messages .= __('Erro (%1): %2', $errorCount, $msg['text']);
            $errorCount++;
        }
        $this->message = $messages;
        $this->updateShipment($shipment, $this->message, Shipment::STATUS_ERROR);
    }

    /**
     * @param $shipment
     * @param $responseStatus
     * @param $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateShipment($shipment, $message, $status)
    {
        /** @var Shipment $shipment */
        $shipment = $this->shipmentRepository->getById($shipment->getId());
        $shipment->setIntelipostStatus($status);
        $shipment->setIntelipostMessage($message);
        $this->shipmentRepository->save($shipment);
    }

    /**
     * @param $orderIncrementId
     * @param $status
     * @param $comment
     */
    public function updateOrder($orderIncrementId, $message, $status)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->helper->loadOrder($orderIncrementId);
        $this->helper->updateOrder($order->getId(), $status, $message);
    }

    /**
     * @return string
     */
    public function getErrorMessages()
    {
        return $this->message;
    }
}
