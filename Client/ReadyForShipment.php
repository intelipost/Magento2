<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Model\Shipment;

class ReadyForShipment extends AbstractShipment
{
    /**
     * @param $shipment
     * @throws \Exception
     */
    public function readyForShipmentRequestBody($shipment)
    {
        $requestBody = $this->prepareRequestBody($shipment);
        $this->sendReadyForShipmentRequest($this->helper->serializeData($requestBody), $shipment);
        return $this;
    }

    /**
     * @param $requestBody
     * @param $shipment
     * @throws \Exception
     */
    public function sendReadyForShipmentRequest($requestBody, $shipment)
    {
        $method = 'shipment_order/multi/ready_for_shipment/with_date';
        $response = $this->helperApi->apiRequest('POST', $method, $requestBody);
        $result = $this->helper->unserializeData($response);
        $responseStatus = $result['status'];

        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_ERROR) {
            $this->setError($shipment, $result);
        }

        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_OK) {
            $this->updateShipment($shipment, $responseStatus, Shipment::STATUS_READY_FOR_SHIPMENT);
            $status = $this->helper->getConfig('ready_to_ship_status', 'order_status', 'intelipost_push');
            $comment = __('Order set ready for shipment on Intelipost');
            $this->updateOrder($shipment->getData('order_increment_id'), $comment, $status);
        }
    }
}
