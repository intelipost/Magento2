<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

class Shipped
{
    /** @var string */
    protected $message;

    /** @var \Intelipost\Shipping\Helper\Data  */
    protected $helper;

    /** @var \Intelipost\Shipping\Helper\Api  */
    protected $helperApi;

    /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface  */
    protected $timezone;

    /** @var \Intelipost\Shipping\Api\ShipmentRepositoryInterface  */
    protected $shipmentRepository;

    /**
     * @param \Intelipost\Shipping\Helper\Api $helperApi
     * @param \Intelipost\Shipping\Helper\Data $helper
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Intelipost\Shipping\Helper\Api $helperApi,
        \Intelipost\Shipping\Helper\Data $helper,
        \Intelipost\Shipping\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    )
    {
        $this->helper = $helper;
        $this->helperApi = $helperApi;
        $this->timezone = $timezone;
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param $shipment
     * @throws \Exception
     */
    public function shippedRequestBody($shipment)
    {
        $requestBody = $this->prepareShippedRequestBody($shipment);
        $this->sendShippedRequest(json_encode($requestBody), $shipment);
        return $this;
    }

    /**
     * @param $shipment
     * @return \stdClass[]
     */
    public function prepareShippedRequestBody($shipment)
    {
        $date = $this->timezone->date();
        $eventDate = $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);

        $body = new \stdClass();
        $body->order_number = $shipment->getData('order_increment_id');
        $body->event_date  = str_replace(' ', 'T', $eventDate);

        return [$body];
    }

    /**
     * @param $requestBody
     * @param $shipment
     * @throws \Exception
     */
    public function sendShippedRequest($requestBody, $shipment)
    {
        $response = $this->helperApi->apiRequest('POST', 'shipment_order/multi/shipped/with_date', $requestBody);
        $result = json_decode($response);

        if($result->status == 'ERROR') {
            $messages = null;
            $errorCount = 1;

            foreach ($result->messages as $_message) {
                $messages .= __('Erro (%1): %2', $errorCount, $_message->text);
                $errorCount++;
            }
            $this->message = $messages;

            /** @var \Intelipost\Shipping\Model\Shipment $shipment */
            $shipment = $this->shipmentRepository->getById($shipment->getId());
            $shipment->setIntelipostStatus(\Intelipost\Shipping\Model\Shipment::STATUS_ERROR);
            $shipment->setIntelipostMessage($this->message);
            $this->shipmentRepository->save($shipment);

        }

        if($result->status == 'OK') {
            /** @var \Intelipost\Shipping\Model\Shipment $shipment */
            $shipment = $this->shipmentRepository->getById($shipment->getId());
            $shipment->setIntelipostStatus(\Intelipost\Shipping\Model\Shipment::STATUS_SHIPPED);
            $shipment->setIntelipostMessage($result->status);
            $this->shipmentRepository->save($shipment);
        }
    }

    public function getErrorMessages()
    {
        return $this->message;
    }
}
