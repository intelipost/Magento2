<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Helper;

use Intelipost\Shipping\Api\QuoteRepositoryInterface;
use Intelipost\Shipping\Model\QuoteFactory;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Convert\Order as ConvertOrder;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentRepository as OrderShipmentRepository;
use Magento\Sales\Model\Order\ShipmentFactory as OrderShipmentFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const RESULT_QUOTES = 'intelipost_result_shippings';
    const RESULT_PICKUP = 'intelipost_result_pickup';

    /** @var QuoteFactory */
    protected $quoteFactory;

    /** @var QuoteRepositoryInterface */
    protected $quoteRepository;

    /** @var OrderRepositoryInterface */
    protected $orderRepository;

    /** @var SessionManager */
    protected $sessionManager;

    /** @var Quote */
    protected $backendSession;

    /** @var Session */
    protected $checkoutSession;

    /** @var CustomerSession */
    protected $customerSession;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var GroupRepository */
    protected $customerGroupRepository;

    /** @var State */
    protected $state;

    /** @var Json */
    protected $json;

    /** @var OrderInterface */
    protected $order;

    /** @var OrderShipmentRepository */
    protected $orderShipmentRepository;

    /** @var OrderShipmentFactory */
    protected $orderShipmentFactory;

    /** @var ShipmentNotifier */
    protected $shipmentNotifier;

    /** @var ConvertOrder */
    protected $convertOrder;

    /** @var TrackFactory */
    protected $trackFactory;

    /** @var array */
    protected $selectedSchedulingMethod = [];

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param QuoteFactory $quoteFactory
     * @param QuoteRepositoryInterface $quoteRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderShipmentRepository $orderShipmentRepository
     * @param OrderShipmentFactory $orderShipmentFactory
     * @param ShipmentNotifier $shipmentNotifier
     * @param ConvertOrder $convertOrder
     * @param TrackFactory $trackFactory
     * @param SessionManager $sessionManager
     * @param Quote $backendSession
     * @param Json $json
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param GroupRepository $customerGroupRepository
     * @param OrderInterface $order
     * @param State $state
     */
    public function __construct(
        Context $context,
        QuoteFactory $quoteFactory,
        QuoteRepositoryInterface $quoteRepository,
        OrderRepositoryInterface $orderRepository,
        OrderShipmentRepository $orderShipmentRepository,
        OrderShipmentFactory $orderShipmentFactory,
        ShipmentNotifier $shipmentNotifier,
        ConvertOrder $convertOrder,
        TrackFactory $trackFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SessionManager $sessionManager,
        Quote $backendSession,
        Json $json,
        Session $checkoutSession,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        GroupRepository $customerGroupRepository,
        OrderInterface $order,
        State $state
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->json = $json;
        $this->orderRepository = $orderRepository;
        $this->sessionManager = $sessionManager;
        $this->backendSession = $backendSession;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->orderShipmentRepository = $orderShipmentRepository;
        $this->orderShipmentFactory = $orderShipmentFactory;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->order = $order;
        $this->customerGroupRepository = $customerGroupRepository;

        parent::__construct($context);
    }

    public function getStreetAttribute(): int
    {
        $streetAttribute = (int) $this->getConfig('street_attribute');
        if (!$streetAttribute) {
            $streetAttribute = 1;
        }
        return $streetAttribute;
    }

    public function getNumberAttribute(): int
    {
        $numberAttribute = (int) $this->getConfig('number_attribute');
        if (!$numberAttribute) {
            $numberAttribute = 2;
        }
        return $numberAttribute;
    }

    public function getComplementAttribute(): int
    {
        $complementAttribute = (int) $this->getConfig('complement_attribute');
        if (!$complementAttribute) {
            $complementAttribute = 3;
        }
        return $complementAttribute;
    }

    public function getDistrictAttribute(): int
    {
        $districtAttribute = (int) $this->getConfig('district_attribute');
        if (!$districtAttribute) {
            $districtAttribute = 4;
        }
        return $districtAttribute;
    }

    /**
     * @return string[]
     */
    public function getPreDispatchEvents()
    {
        return ['NEW', 'READY_FOR_SHIPPING', 'SHIPPED'];
    }

    /**
     * @return string[]
     */
    public function getPostDispatchEvents()
    {
        return ['TO_BE_DELIVERED', 'IN_TRANSIT', 'DELIVERED', 'CLARIFY_DELIVERY_FAIL', 'DELIVERY_FAILED'];
    }

    /**
     * @param string $carrier
     * @param string $description
     * @param $estimatedDelivery
     * @param false $scheduled
     * @return mixed|string
     */
    public function getCustomCarrierTitle($carrier, $description, $estimatedDelivery, $scheduled = false)
    {
        $estimatedDelivery = (int) $estimatedDelivery;
        if ($scheduled) {
            $text = $this->getConfig('scheduled_title');
        } else {
            $aditionalDeliveryDate = (int)$this->scopeConfig->getValue('carriers/intelipost/additional_delivery_date');
            $estimatedDelivery = $estimatedDelivery + $aditionalDeliveryDate;

            $methodCustomTitle = (string) $this->getConfig('custom_title', $carrier);
            if (!$estimatedDelivery) {
                $sameDayTitle = (string) $this->getConfig('same_day_title', $carrier);
                $methodCustomTitle = sprintf($sameDayTitle, $description);
            }
            $text = str_replace(['{method}', '{days}'], [$description, $estimatedDelivery], $methodCustomTitle);
        }

        return $text;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionManager->getSessionId();
    }

    /**
     * @param string $key
     * @return array|mixed
     */
    public function getResultQuotes($key = self::RESULT_QUOTES)
    {
        $result = $this->customerSession->getData($key);
        return !empty($result) ? $result : [];
    }

    /**
     * @param $carrier
     * @param $id
     * @param $method
     * @param $postData
     * @param false $volumes
     * @return \Intelipost\Shipping\Model\Quote|null
     */
    public function saveQuote($carrier, $id, $method, $postData, $volumes = false)
    {
        $intelipostQuote = $this->quoteFactory->create();

        $intelipostQuote->setSessionId($this->getSessionId());
        $intelipostQuote->setCarrier($carrier);
        $intelipostQuote->setQuoteId($id);
        $intelipostQuote->setProducts($this->serializeData($postData['products']));
        $intelipostQuote->setOriginZipCode($postData['origin_zip_code']);

        $intelipostQuote->setLogisticProviderName($method['logistic_provider_name']);
        $intelipostQuote->setDescription($method['description']);

        $intelipostQuote->setDeliveryMethodId($method['delivery_method_id']);
        $intelipostQuote->setDeliveryEstimateBusinessDays($method['delivery_estimate_business_days']);

        if (array_key_exists('delivery_estimate_date_exact_iso', $method)) {
            $intelipostQuote->setDeliveryExactEstimatedDate($method['delivery_estimate_date_exact_iso']);
        }

        $intelipostQuote->setDeliveryMethodName($method['delivery_method_name']);

        if (array_key_exists('delivery_method_type', $method)) {
            $intelipostQuote->setDeliveryMethodType($method['delivery_method_type']);
        }

        $intelipostQuote->setAvailableSchedulingDates($method['available_scheduling_dates']);

        $intelipostQuote->setProviderShippingCost($method['provider_shipping_cost']);
        $intelipostQuote->setFinalShippingCost($method['final_shipping_cost']);

        $apiRequest = $this->serializeData($postData['api_request']);
        $intelipostQuote->setApiRequest($apiRequest);

        $apiResponse = $this->serializeData($postData['api_response']);
        $intelipostQuote->setApiResponse($apiResponse);

        if (!empty($this->selectedSchedulingMethod)) {
            if ($method['delivery_method_id'] == $this->selectedSchedulingMethod['delivery_method_id']) {
                $intelipostQuote->setSelectedSchedulingDates(
                    $this->selectedSchedulingMethod['selected_scheduling_dates']
                );
                $intelipostQuote->setSelectedSchedulingPeriod(
                    $this->selectedSchedulingMethod['selected_scheduling_period']
                );
            }
        }

        $intelipostQuote->setQuoteVolume($this->serializeData($volumes));

        if ($this->getConfig('save_quote_database', 'settings', 'intelipost_basic')) {
            $this->quoteRepository->save($intelipostQuote);
        }

        return $intelipostQuote;
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function serializeData($data)
    {
        try {
            $serializedData = is_string($data) ? $data : $this->json->serialize($data);
        } catch (\Exception $e) {
            $serializedData = '{}';
        }
        return $serializedData;
    }

    /**
     * @param $data
     * @return array|object
     */
    public function unserializeData($data)
    {
        try {
            $unserializedData = $this->json->unserialize($data);
        } catch (\Exception $e) {
            $unserializedData = [];
        }
        return $unserializedData;
    }

    /**
     * @param array|null $data
     */
    public function saveResultQuotes(array $data = null)
    {
        $this->customerSession->setData(self::RESULT_QUOTES, $data);
    }

    /**
     * @param $response
     */
    public function checkFreeShipping($response)
    {
        $freeshippingMethod = $this->getConfig('freeshipping_method');
        $freeshippingText = $this->getConfig('freeshipping_text');

        $lowerPrice = PHP_INT_MAX;
        $lowerDeliveryDate = PHP_INT_MAX;
        $lowerMethod = null;

        foreach ($response['content']['delivery_options'] as $child) {
            $deliveryMethodId = $child['delivery_method_id'];
            $finalShippingCost = $child['final_shipping_cost'];
            $deliveryEstimateDays = $child['delivery_estimate_business_days'];

            switch ($freeshippingMethod) {
                case 'lower_price':
                    if ($finalShippingCost < $lowerPrice) {
                        $lowerPrice = $finalShippingCost;
                        $lowerMethod = $deliveryMethodId;
                    }
                    break;
                case 'lower_delivery_date':
                    if ($deliveryEstimateDays < $lowerDeliveryDate) {
                        $lowerDeliveryDate = $deliveryEstimateDays;
                        $lowerMethod = $deliveryMethodId;
                    }
                    break;
            }
        }

        foreach ($response['content']['delivery_options'] as $id => $child) {
            $deliveryMethodId = $child['delivery_method_id'];
            if ($deliveryMethodId == $lowerMethod) {
                $response['content']['delivery_options'][$id]['final_shipping_cost'] = 0;
                $response['content']['delivery_options'][$id]['description'] = $freeshippingText;
                break;
            }
        }

        return $response;
    }

    /**
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDiscountAmount()
    {
        return ($this->getQuote()->getBaseSubtotal() - $this->getQuote()->getBaseSubtotalWithDiscount()) * -1;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if ($this->isAdmin()) {
            $quote = $this->backendSession->getQuote();
        } else {
            $quote = $this->checkoutSession->getQuote();
        }

        return $quote;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAdmin()
    {
        return $this->state->getAreaCode() == \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
    }

    /**
     * Get Subtotal Price
     * @param int $defaultPrice
     * @return float|int|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSubtotalAmount($defaultPrice = 0)
    {
        $result = $this->getQuote()->getBaseSubtotal();
        if (intval($result) > 0) {
            return $result;
        }

        return $defaultPrice;
    }

    /**
     * @param $orderIncrementId
     * @param $trackingUrl
     * @return \Magento\Framework\Phrase|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrderShipment($orderIncrementId, $trackingUrl)
    {
        $message = '';
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->loadOrder($orderIncrementId);

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

            $track = $this->getTrack($trackingUrl);
            $shipment->addTrack($track);

            try {
                $this->orderShipmentRepository->save($shipment);
                $this->orderRepository->save($shipment->getOrder());
            } catch (\Exception $e) {
                $this->getLogger()->error($e->getMessage());
                $message = __($e->getMessage());
            }
        }

        return $message;
    }

    /**
     * @param $orderIncrementId
     * @param $trackingUrl
     * @return \Magento\Sales\Model\Order\Shipment\Track
     */
    protected function getTrack($trackingUrl)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $this->trackFactory->create();
        $track->setNumber($trackingUrl);
        $track->setCarrierCode('intelipost_shipping');
        $track->setTitle(__('Tracking Status'));
        $track->setDescription(__('Intelipost Tracking Status'));

        return $track;
    }

    /**
     * @param array|null $additional
     * @return array|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAdditionalInformation(array $additional = null)
    {
        $result = [
            'client_type' => $this->getCustomerGroup(),
            'sales_channel' => $this->getStoreName()
        ];

        $additionalData = (is_array($additional) ? $additional : []);

        if (count($additionalData) > 0) {
            $result = array_merge($result, $additional);
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroup()
    {
        if ($this->isAdmin()) {
            $currentGroupId = $this->backendSession->getQuote()->getCustomerGroupId();
        } else {
            $currentGroupId = $this->customerSession->getCustomer()->getGroupId();
        }

        /** @var \Magento\Customer\Model\Group $customerGroup */
        $customerGroup = $this->customerGroupRepository->getById($currentGroupId);
        return strtolower($customerGroup->getCode());
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreName()
    {
        $salesChannel = $this->getConfig('sales_channel');
        if (!$salesChannel) {
            $salesChannel = $this->storeManager->getStore()->getName();
        }
        return $salesChannel;
    }

    /**
     * @param $orderId
     * @param $status
     * @param $comment
     */
    public function updateOrder($orderId, $status, $comment)
    {
        if (!$status) {
            $status = false;
        }
        $notifyCustomer = (bool)$this->getConfig('notify_customer');
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        $order->addCommentToStatusHistory($comment, $status, $notifyCustomer);
        $this->orderRepository->save($order);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPageIdentification()
    {
        $result = [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? $this->getQuote()->getRemoteIp(),
            'session' => $this->getSessionId(),
            'page_name' => $this->getPageName(),
            'url' => $this->getCurrentUrl()
        ];

        return $result;
    }

    /**
     * @param $config
     * @param string $group
     * @param string $section
     * @return mixed
     */
    public function getConfig($config, $group = 'intelipost', $section = 'carriers')
    {
        return $this->scopeConfig->getValue(
            $section . '/' . $group . '/' . $config,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageName()
    {
        $result = 'checkout';

        if ($this->isAdmin()) {
            $result = 'admin';
        } else {
            $originalPathInfo = (string) $this->_request->getOriginalPathInfo();
            if (!strcmp($originalPathInfo, '/intelipost/product/shipping/')) {
                $result = 'product';
            }
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentUrl()
    {
        $url = $this->storeManager->getStore()->getCurrentUrl();
        $result = urldecode(htmlspecialchars_decode($url));
        return $result;
    }

    /**
     * @return mixed|null
     */
    public function haveData()
    {
        $args = func_get_args();
        $result = null;

        foreach ($args as $_arg) {
            if (!empty($_arg)) {
                $result = $_arg;
                break;
            }
        }

        return $result;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @param string
     */
    public function log($message)
    {
        if ($this->getConfig('debug')) {
            $this->getLogger()->info($message);
        }
    }

    /**
     * @param $incrementId
     * @return OrderInterface
     */
    public function loadOrder($incrementId)
    {
        return $this->order->loadByIncrementId($incrementId);
    }
}
