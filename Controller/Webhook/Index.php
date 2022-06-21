<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
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

class Index extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /** @var Data  */
    protected $helper;

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

    public function __construct(
        Context $context,
        Data $helper,
        ShipmentRepository $shipmentRepository,
        WebhookRepository $webhookRepository,
        WebhookFactory $webhookFactory,
        Json $json
    ) {
        parent::__construct($context);
        $this->json = $json;
        $this->helper = $helper;
        $this->shipmentRepository = $shipmentRepository;
        $this->webhookRepository = $webhookRepository;
        $this->webhookFactory = $webhookFactory;
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
                    if ($status) {
                        $trackingUrl = $requestBody['tracking_url'] ?? null;
                        $this->createShipment($orderIncrementId, $trackingUrl);
                    }
                    break;

                case 'IN_TRANSIT':
                    $status = $this->helper->getConfig('status_in_transit');
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
            try {
                $shipment = $this->shipmentRepository->getByOrderIncrementId($incrementId);
                $shipment->setTrackingCode($trackingCode);
                $shipment->setTrackingUrl($trackingUrl);
                $this->shipmentRepository->save($shipment);
            } catch (\Exception $e) {
                $this->helper->log($e->getMessage());
            }
        }
    }

    /**
     * @param $orderIncrementId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipment($orderIncrementId, $trackingUrl)
    {
        $message = $this->helper->createOrderShipment($orderIncrementId, $trackingUrl);
        $this->saveWebhookMessage($message);
    }
}
