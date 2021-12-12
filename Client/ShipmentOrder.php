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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

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

    /** @var OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

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
        OrderRepositoryInterface $orderRepository,
        Data $helper
    )
    {
        $this->shipmentCustomer = $customer;
        $this->shipmentVolume = $volume;
        $this->shipmentInvoice = $shipmentInvoice;
        $this->helper = $helper;
        $this->helperApi = $helperApi;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
    }

    /**
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     */
    public function execute($shipment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->helper->loadOrder($shipment->getData('order_increment_id'));
        $shipment->addData([
            'order_entity_id' => $order->getId(),
            'order_created_at' => $order->getCreatedAt(),
            'shipping_amount' => $order->getShippingAmount(),
            'status' => $order->getStatus(),
            'customer_firstname' => $order->getCustomerFirstname(),
            'customer_lastname' => $order->getCustomerLastname(),
            'customer_email' => $order->getCustomerEmail(),
            'customer_taxvat' => $order->getCustomerTaxvat(),
            'base_grand_total' =>  $order->getBaseGrandTotal(),
            'increment_id' => $order->getIncrementId()
        ]);

        $requestBody = $this->getShipment($shipment);
        $this->sendShipmentRequest(json_encode($requestBody), $shipment);
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

        $shippedDate = null;
        if ($this->helper->getConfig('create_and_ship','order_status','intelipost_push')) {
            $shippedDate = $this->getNowDateTime();
        }
        $created = $this->getNowDateTime();

        $body = new \stdClass();
        $body->order_number = $shipment->getData('order_increment_id');
        $body->sales_order_number = $shipment->getData('increment_id');
        $body->quote_id = $shipment->getData('quote_id');
        $body->delivery_method_id = $shipment->getData('delivery_method_id');
        $body->estimated_delivery_date = str_replace(' ', 'T', $shipment->getData('delivery_estimate_date_exact_iso'));
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
        $body->shipped_date = $shippedDate;
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
        $response = $this->helperApi->apiRequest('POST', 'shipment_order', $requestBody);
        $result = json_decode($response);
        $shipmentStatus = \Intelipost\Shipping\Model\Shipment::STATUS_CREATED;
        $shipmentMessage = 'Ok.';
        $trackingCodes = '';

        if ($result->status == 'ERROR') {
            $messages = null;
            $errorCount = 1;

            foreach ($result->messages as $_message) {
                $messages .= ' Erro (' . $errorCount . '): ' . $_message->text . "</br>";
                $errorCount++;
            }
            $this->message = $messages;

            $shipmentStatus = \Intelipost\Shipping\Model\Shipment::STATUS_ERROR;
            $shipmentMessage = str_replace('</br>', '', $messages);

        } else if ($result->status == 'OK') {
            $trackingUrl = $result->content->tracking_url;

            $orderId = $shipment->getId();
            $status = $this->helper->getConfig('magento_status_after_create','order_status', 'intelipost_push');
            $this->updateOrderStatus($orderId, $status);
        }

        $this->updateShipment($shipment->getId(), $shipmentStatus, $shipmentMessage, $trackingUrl);
    }

    /**
     * @param $shipmentId
     * @param $status
     * @param $message
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateShipment($shipmentId, $status, $message, $trackingUrl)
    {
        /** @var \Intelipost\Shipping\Model\Shipment $shipmentModel */
        $shipmentModel = $this->shipmentRepository->getById($shipmentId);
        $shipmentModel->setIntelipostStatus($status);
        $shipmentModel->setIntelipostMessage($message);
        if (!empty($trackingUrl)) {
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
        $order = $this->orderRepository->get($orderId);
        $order->addCommentToStatusHistory(
            __('Order status updated to %1 by Intelipost Module', __($status)),
            $status
        );
        $this->orderRepository->save($order);
    }

    /**
     * @return array|string|string[]
     * @throws \Exception
     */
    public function getNowDateTime()
    {
        $currentDateTimeUTC = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $localizedDateTimeISO = $this->timezone->date(new \DateTime($currentDateTimeUTC))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $now = str_replace(' ', 'T', $localizedDateTimeISO);
        return $now;
    }
}
