<?php
/*
 * @package     Intelipost_Push
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Controller\Webhook;

use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\ShipmentRepository;
use Intelipost\Shipping\Model\ResourceModel\WebhookRepository;
use Intelipost\Shipping\Model\WebhookFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\ShipmentRepository as OrderShipmentRepository;
use Magento\Sales\Model\Order\Shipment\TrackFactory;

class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /** @var Data  */
    protected $helper;

    /** @var ConvertOrder  */
    protected $convertOrder;

    /** @var TrackFactory  */
    protected $track;

    /** @var Json  */
    protected $json;

    /** @var ShipmentRepository  */
    private $shipmentRepository;

    /** @var WebhookRepository  */
    private $webhookRepository;

    /** @var WebhookFactory  */
    private $webhookFactory;

    /** @var \Intelipost\Shipping\Model\Webhook  */
    private $webhook;

    /** @var OrderShipmentRepository  */
    private $orderShipmentRepository;

    /** @var OrderRepository  */
    private $orderRepository;

    public function __construct
    (
        Context $context,
        Data $helper,
        ShipmentRepository $shipmentRepository,
        WebhookRepository $webhookRepository,
        WebhookFactory $webhookFactory,
        OrderShipmentRepository $orderShipmentRepository,
        OrderRepository $orderRepository,
        ConvertOrder $convertOrder,
        Json $json,
        TrackFactory $track
    )
    {
        parent::__construct($context);
        $this->json = $json;
        $this->helper = $helper;
        $this->shipmentRepository = $shipmentRepository;
        $this->webhookRepository = $webhookRepository;
        $this->webhookFactory = $webhookFactory;
        $this->orderShipmentRepository = $orderShipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->convertOrder = $convertOrder;
        $this->track = $track;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHttpResponseCode(403);
        return new InvalidRequestException(
            $result
        );
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $authUser = $request->getServer('PHP_AUTH_USER');
        $configApiKey = $this->helper->getConfig('api_key', 'settings', 'intelipost_basic');
        return ($authUser == $configApiKey);
    }

    public function execute()
    {
        $webhookEnabled = $this->helper->getConfig('webhook_enabled');

        if ($webhookEnabled) {
            try {
                /** @var RequestInterface $request */
                $request = $this->getRequest();

                $body = $this->getContent($request);
                $trackingCode = $body['tracking_code'] ?? null;
                $trackingUrl = $body['tracking_url'] ?? null;
                $incrementId = $body['order_number'] ?? null;
                $status = isset($body['history']) ? $body['history']['shipment_order_volume_state'] : null;

                $this->saveWebhook($request->getContent(), $incrementId, $status);

                $this->updateTrackingCode($incrementId, $trackingCode, $trackingUrl);

                $this->updateOrderStatus($incrementId, $body);
            } catch (\Exception $e) {
                $this->helper->getLogger()->error($e->getMessage());
                $this->saveWebhookMessage($e->getMessage());
            }

        }
    }

    /**
     * @param $requestBody
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveWebhook($requestBody, $incrementId, $status)
    {
        /** @var \Intelipost\Shipping\Model\Webhook $webhook */
        $this->webhook = $this->webhookFactory->create();
        $this->webhook->setPayload($requestBody);
        $this->webhook->setOrderIncrementId($incrementId);
        $this->webhook->setStatus($status);
        $this->webhookRepository->save($this->webhook);
    }

    protected function saveWebhookMessage($message)
    {
        if ($this->webhook) {
            $this->webhook->setMessage($message);
            $this->webhookRepository->save($this->webhook);
        }
    }

    /**
     * @param $orderIncrementId
     * @param $requestBody
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function updateOrderStatus($orderIncrementId, $requestBody)
    {
        $preDispatchEvents = $this->helper->getPreDispatchEvents();
        $postDispatchEvents = $this->helper->getPostDispatchEvents();

        $trackPreShip = $this->helper->getConfig('track_pre_ship');

        $state = $requestBody['history']['shipment_order_volume_state'];
        $statusDefaultName = $requestBody['history']['shipment_volume_micro_state']['default_name'];
        $comment = __('[Intelipost Webhook] new status received: %1', $statusDefaultName);

        if (
            (in_array($state, $preDispatchEvents) && $trackPreShip)
            || (in_array($state, $postDispatchEvents))
        ) {
            switch (strtoupper($state)) {
                case 'NEW':
                    $status = $this->helper->getConfig('status_created');
                    break;

                case 'READY_FOR_SHIPPING':
                    $status = $this->helper->getConfig('status_ready_for_shipment');
                    break;

                case 'SHIPPED':
                    $status = $this->helper->getConfig('status_shipped');
                    $statusShipmentAfterIpShipped = $this->helper->getConfig('create_shipment_after_ip_shipped');
                    if ($statusShipmentAfterIpShipped) {
                        $trackingUrl = $requestBody['tracking_url'] ?? null;
                        $this->createShipment($orderIncrementId, $trackingUrl);
                    }
                    break;

                case 'IN_TRANSIT':
                    $status = $this->helper->getConfig('status_in_transit');;
                    break;

                case 'TO_BE_DELIVERED':
                    $status = $this->helper->getConfig('status_to_be_delivered');
                    break;

                case 'DELIVERED':
                    $status = $this->helper->getConfig('status_delivered');
                    break;

                case 'CLARIFY_DELIVERY_FAIL':
                    $status = $this->helper->getConfig('status_clarify_delivery_failed');
                    break;

                case 'DELIVERY_FAILED':
                    $status = $this->helper->getConfig('status_delivery_failed');
                    break;
            }

            $this->updateOrder($orderIncrementId, $status, $comment);
        }
    }

    /**
     * @param RequestInterface $request
     * @return mixed|string
     */
    protected function getContent(RequestInterface $request)
    {
        try {
            $content = $request->getContent();
            return $this->json->unserialize($content);
        } catch (\Exception $e) {
            $this->helper->getLogger()->critical($e->getMessage());
        }

        return '';
    }

    /**
     * @param $orderIncrementId
     * @param $status
     * @param $comment
     */
    public function updateOrder($orderIncrementId, $status, $comment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->helper->loadOrder($orderIncrementId);
        $this->helper->updateOrder($order->getId(), $status, $comment);
    }

    /**
     * @param $incrementId
     * @param $trackingCode
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function updateTrackingCode($incrementId, $trackingCode, $trackingUrl)
    {
        if ($trackingCode || $trackingUrl) {
            $shipment = $this->shipmentRepository->getByOrderIncrementId($incrementId);
            $shipment->setTrackingCode($trackingCode);
            $shipment->setTrackingUrl($trackingUrl);
            $this->shipmentRepository->save($shipment);
        }
    }

    /**
     * @param $orderIncrementId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipment($orderIncrementId, $trackingUrl)
    {
        $message = '';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->helper->loadOrder($orderIncrementId);

        if (!$order->canShip()) {
            $message = __('It\'s not possible to create a shipment on this order.');
        } else {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $shipment = $this->convertOrder->toShipment($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }

                $qtyShipped = $orderItem->getQtyToShip();
                $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                $shipment->addItem($shipmentItem);
            }

            $shipment->register();
            $shipment->getOrder()->setIsInProcess(true);

            $track = $this->track->create();
            $track->setNumber($order->getIncrementId());
            $track->setCarrierCode('intelipost_shipping');
            $track->setTitle(__('Tracking Status'));
            $track->setDescription(__('Intelipost Tracking Status'));
            $track->setUrl($trackingUrl);
            $shipment->addTrack($track);

            try {
                $this->orderShipmentRepository->save($shipment);
                $this->orderRepository->save($shipment->getOrder());

            } catch (\Exception $e) {
                $this->helper->getLogger()->error($e->getMessage());
                $message = __($e->getMessage());
            }
        }

        $this->saveWebhookMessage($message);
    }
}
