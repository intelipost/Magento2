<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Helper\Api;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Api\ShipmentRepositoryInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class ShipmentOrder
{
    protected $message = '';

    /** @var ShipmentOrder\Customer */
    protected $shipmentCustomer;

    /** @var ShipmentOrder\Volume */
    protected $shipmentVolume;

    /** @var ShipmentOrder\Invoice */
    protected $shipmentInvoice;

    /** @var Data */
    protected $helper;

    /** @var Api */
    protected $helperApi;

    /** @var ShipmentRepositoryInterface */
    protected $shipmentRepository;

    /** @var TimezoneInterface */
    protected $timezone;

    /**
     * @param ShipmentOrder\Customer $customer
     * @param ShipmentOrder\Volume $volume
     * @param ShipmentOrder\Invoice $shipmentInvoice
     * @param TimezoneInterface $timezone
     * @param Api $helperApi
     * @param ShipmentRepositoryInterface $shipment
     * @param Data $helper
     */
    public function __construct(
        ShipmentOrder\Customer $customer,
        ShipmentOrder\Volume $volume,
        ShipmentOrder\Invoice $shipmentInvoice,
        TimezoneInterface $timezone,
        Api $helperApi,
        ShipmentRepositoryInterface $shipmentRepository,
        Data $helper
    ) {
        $this->shipmentCustomer = $customer;
        $this->shipmentVolume = $volume;
        $this->shipmentInvoice = $shipmentInvoice;
        $this->helper = $helper;
        $this->helperApi = $helperApi;
        $this->shipmentRepository = $shipmentRepository;
        $this->timezone = $timezone;
    }

    /**
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     */
    public function execute($shipment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->helper->loadOrder($shipment->getData('order_increment_id'));

        $customerTaxvat = $order->getCustomerTaxvat();
        $customerAttribute = $this->helper->getConfig('federal_tax_payer_id', 'attributes', 'intelipost_push');
        if ($customerAttribute) {
            $customer = $order->getCustomer();
            if ($customer) {
                if ($customer->getData($customerAttribute)) {
                    $customerTaxvat = $customer->getData($customerAttribute);
                }
            } else {
                if ($order->getData('customer_' . $customerAttribute)) {
                    $customerTaxvat = $order->getData('customer_' . $customerAttribute);
                }
            }
        }

        $shipment->addData([
            'order_entity_id' => $order->getId(),
            'order_created_at' => $order->getCreatedAt(),
            'shipping_amount' => $order->getShippingAmount(),
            'status' => $order->getStatus(),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_taxvat' => $customerTaxvat,
            'base_grand_total' =>  $order->getBaseGrandTotal(),
            'increment_id' => $order->getIncrementId()
        ]);

        $requestBody = $this->getShipment($shipment);
        $this->sendShipmentRequest($this->helper->serializeData($requestBody), $shipment);
        return $this;
    }

    /**
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     * @return \stdClass
     * @throws \Exception
     */
    public function getShipment($shipment)
    {
        $customerData = $this->shipmentCustomer->getInformation(
            $shipment->getData('order_entity_id'),
            $shipment->getCustomerTaxvat()
        );

        $created = $this->getNowDateTime();

        $estimateDate = (string) $shipment->getData('delivery_estimate_date_exact_iso');

        $body = new \stdClass();
        $body->order_number = $shipment->getData('order_increment_id');
        $body->sales_order_number = $shipment->getData('increment_id');
        $body->quote_id = $shipment->getData('quote_id');
        $body->delivery_method_id = $shipment->getData('delivery_method_id');
        $body->estimated_delivery_date = str_replace(' ', 'T', $estimateDate);
        $body->customer_shipping_costs = $shipment->getData('customer_shipping_costs');
        $body->provider_shipping_costs = $shipment->getData('provider_shipping_costs');
        $body->sales_channel = $shipment->getData('sales_channel');
        $body->scheduled = (int)$shipment->getData('scheduled');
        $body->scheduling_window_start = $shipment->getData('scheduling_window_start');
        $body->scheduling_window_end = $shipment->getData('scheduling_window_end');
        $body->shipment_order_type = $shipment->getData('shipment_order_type');
        $body->shipment_order_sub_type = $shipment->getData('shipment_order_sub_type');
        $body->end_customer = $customerData;
        $body->shipment_order_volume_array = $this->getVolumes($shipment);
        $body->created = $created;

        return $body;
    }

    /**
     * @param $shipment
     * @return array
     */
    public function getVolumes($shipment)
    {
        $volume = $this->shipmentInvoice->getInformation($shipment->getData('order_increment_id'));
        return $this->shipmentVolume->getInformation(
            $shipment->getData('volumes'),
            $volume
        );
    }

    /**
     * @param $requestBody
     * @param $shipment
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sendShipmentRequest($requestBody, $shipment)
    {
        $trackingCode = null;
        $trackingUrl = null;

        $response = $this->helperApi->apiRequest('POST', 'shipment_order', $requestBody);
        $result = $this->helper->unserializeData($response);

        $shipmentStatus = \Intelipost\Shipping\Model\Shipment::STATUS_CREATED;
        $responseStatus = $result['status'];
        $shipmentMessage = $responseStatus;

        if ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_ERROR) {
            $messages = null;
            $errorCount = 1;

            foreach ($result['messages'] as $msg) {
                $messages .= __('Error (%1): %2', $errorCount, $msg['text']) . " \n";
                $errorCount++;
            }
            $this->message = $messages;

            $shipmentStatus = \Intelipost\Shipping\Model\Shipment::STATUS_ERROR;
            $shipmentMessage = $messages;
        } elseif ($responseStatus == \Intelipost\Shipping\Client\Intelipost::RESPONSE_STATUS_OK) {
            $trackingCode = '';
            $trackingUrl = $result['content']['tracking_url'];
            if (isset($result['content']['shipment_order_volume_array'])) {
                $trackingCodes = [];
                foreach ($result['content']['shipment_order_volume_array'] as $volume) {
                    if (isset($volume['tracking_code'])) {
                        $trackingCodes[] = $volume['tracking_code'];
                    }
                }
                if (!empty($trackingCodes)) {
                    $trackingCode = implode(', ', $trackingCodes);
                }
            }

            $incrementId = $shipment->getData('order_increment_id');
            $status = $this->helper->getConfig('created_status', 'order_status', 'intelipost_push');
            $order = $this->helper->loadOrder($incrementId);
            $this->updateOrderStatus($order->getId(), $status);
        }

        $this->updateShipment($shipment->getId(), $shipmentStatus, $shipmentMessage, $trackingCode, $trackingUrl);
    }

    /**
     * @param $shipmentId
     * @param $status
     * @param $message
     * @param $trackingCode
     * @param $trackingUrl
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateShipment($shipmentId, $status, $message, $trackingCode, $trackingUrl)
    {
        /** @var \Intelipost\Shipping\Model\Shipment $shipmentModel */
        $shipmentModel = $this->shipmentRepository->getById($shipmentId);
        $shipmentModel->setIntelipostStatus($status);
        $shipmentModel->setIntelipostMessage($message);
        if ($trackingCode) {
            $shipmentModel->setTrackingCode($trackingCode);
        }
        if ($trackingUrl) {
            $shipmentModel->setTrackingUrl($trackingUrl);
        }
        $this->shipmentRepository->save($shipmentModel);
    }

    /**
     * @return string
     */
    public function getErrorMessages()
    {
        return $this->message;
    }

    /**
     * @param $orderId
     * @param $status
     * @throws \Exception
     */
    public function updateOrderStatus($orderId, $status)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $comment = __('Order created on Intelipost');
        $this->helper->updateOrder($orderId, $status, $comment);
    }

    /**
     * @return array|string|string[]
     * @throws \Exception
     */
    public function getNowDateTime()
    {
        $currentDateTimeUTC = (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        $newDate = new \DateTime($currentDateTimeUTC);
        $localizedDateTimeISO = $this->timezone->date($newDate)->format(DateTime::DATETIME_PHP_FORMAT);
        $now = str_replace(' ', 'T', $localizedDateTimeISO);
        return $now;
    }
}
