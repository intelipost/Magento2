<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Observer\Sales;

use Intelipost\Shipping\Api\Data\QuoteInterface;
use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\Shipment as ShipmentResourceModel;
use Intelipost\Shipping\Model\ShipmentFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class OrderPlaceAfter implements ObserverInterface
{
    /** @var QuoteInterface  */
    protected $intelipostQuote;

    /** @var Data  */
    protected $helper;

    /** @var SessionManager  */
    protected $sessionManager;

    /** @var CookieManagerInterface  */
    protected $cookieManager;

    /** @var ShipmentFactory  */
    protected $shipmentFactory;

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /**
     * @param QuoteInterface $intelipostQuote
     * @param Data $intelipostHelper
     * @param SessionManager $sessionManager
     * @param CookieManagerInterface $cookieManager
     * @param ShipmentFactory $shipmentFactory
     * @param ShipmentResourceModel $shipmentResource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteInterface $intelipostQuote,
        Data $intelipostHelper,
        SessionManager $sessionManager,
        CookieManagerInterface $cookieManager,
        ShipmentFactory $shipmentFactory,
        ShipmentResourceModel $shipmentResource,
        StoreManagerInterface $storeManager
    )
    {
        $this->intelipostQuote = $intelipostQuote;
        $this->helper = $intelipostHelper;
        $this->sessionManager = $sessionManager;
        $this->cookieManager = $cookieManager;
        $this->shipmentFactory = $shipmentFactory;
        $this->shipmentResource = $shipmentResource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getOrder();

            if (!$order->getIsVirtual() && $order->getShippingMethod()) {
                $resultQuotes = [];
                if (strpos($order->getShippingMethod(), '_') !== false) {
                    $deliveryMethodId = explode("_", $order->getShippingMethod());
                    if (count($deliveryMethodId) < 3) {
                        return;
                    }

                    $deliveryMethodId = $deliveryMethodId[count($deliveryMethodId) - 2] .
                        "_" .
                        $deliveryMethodId[count($deliveryMethodId) - 1];

                    foreach ($this->helper->getResultQuotes() as $quote) {
                        if ($quote->getDeliveryMethodId() == $deliveryMethodId && $quote->getOrderId() == null) {
                            $resultQuotes[] = $quote;
                        }
                    }

                    if (empty($resultQuotes) && count($resultQuotes) == 0) {
                        return;
                    }
                }

                $stored = [];
                $resultJson = [];

                $cookie = $this->cookieManager->getCookie(\Intelipost\Shipping\Controller\Schedule\Index::COOKIE_NAME);

                foreach ($resultQuotes as $quoteItem) {
                    if (in_array($quoteItem->getQuoteId(), $stored)) {
                        continue;
                    }

                    $quotes = [];

                    if ($cookie) {
                        $scheduled = explode('+', $cookie);

                        if ($scheduled[0] == $quoteItem->getDeliveryMethodId()) {
                            if (!$quoteItem->getSelectedSchedulingDates() || !$quoteItem->getSelectedSchedulingPeriod()) {
                                $quoteItem->setSelectedSchedulingDates($scheduled[1]);
                                $quoteItem->setSelectedSchedulingPeriod($scheduled[2]);
                            }
                        }
                    }

                    if (count($resultJson) == 0) {
                        $quotes[] = [
                            'quote_id' => $quoteItem->getQuoteId(),
                            'final_shipping_cost' => $quoteItem->getFinalShippingCost(),
                            'provider_shipping_cost' => $quoteItem->getProviderShippingCost(),
                            'delivery_exact_estimated_date' => $quoteItem->getDeliveryExactEstimatedDate(),
                            'delivery_estimated_delivery_business_day' => $quoteItem->getDeliveryEstimateBusinessDays(),
                            'delivery_method_type' => $quoteItem->getDeliveryMethodType(),
                            'products' => $this->helper->unserializeData($quoteItem->getProducts()),
                            'description' => $quoteItem->getDescription(),
                            'delivery_method_name' => $quoteItem->getDeliveryMethodName(),
                            'quote_volume' => $this->helper->unserializeData($quoteItem->getQuoteVolume()),
                            'origin_zip_code' => $quoteItem->getOriginZipCode(),
                            'delivery_method_id' => $quoteItem->getDeliveryMethodId(),
                            'selected_scheduling_dates' => $quoteItem->getSelectedSchedulingDates(),
                            'selected_scheduling_period' => $quoteItem->getSelectedSchedulingPeriod(),
                            'intelipost_status' => 'pending'
                        ];

                        $resultJson = [
                            'session_id' => $quoteItem->getSessionId(),
                            'shipping_method' => $order->getShippingMethod(),
                            'delivery_method_id' => $quoteItem->getDeliveryMethodId(),
                            'total_final_shipping_cost' => $quoteItem->getFinalShippingCost(),
                            'total_provider_shipping_cost' => $quoteItem->getProviderShippingCost(),
                            'order_id' => $order->getIncrementId(),
                            'logistic_provider_name' => $quoteItem->getLogisticProviderName(),
                            'delivery_method_name' => $quoteItem->getDeliveryMethodName(),
                            'selected_scheduling_dates' => $quoteItem->getSelectedSchedulingDates(),
                            'selected_scheduling_period' => $quoteItem->getSelectedSchedulingPeriod(),
                            'quotes' => $quotes
                        ];
                    } else {
                        $quotes = [
                            'quote_id' => $quoteItem->getQuoteId(),
                            'final_shipping_cost' => $quoteItem->getFinalShippingCost(),
                            'provider_shipping_cost' => $quoteItem->getProviderShippingCost(),
                            'delivery_exact_estimated_date' => $quoteItem->getDeliveryExactEstimatedDate(),
                            'delivery_estimated_delivery_business_day' => $quoteItem->getDeliveryEstimateBusinessDays(),
                            'origin_zip_code' => $quoteItem->getOriginZipCode(),
                            'delivery_method_type' => $quoteItem->getDeliveryMethodType(),
                            'products' => $quoteItem->getProducts(),
                            'description' => $quoteItem->getDescription(),
                            'delivery_method_name' => $quoteItem->getDeliveryMethodName(),
                            'quote_volume' => $quoteItem->getQuoteVolume(),
                            'delivery_method_id' => $quoteItem->getDeliveryMethodId(),
                            'selected_scheduling_dates' => $quoteItem->getSelectedSchedulingDates(),
                            'selected_scheduling_period' => $quoteItem->getSelectedSchedulingPeriod(),
                            'intelipost_status' => 'pending'
                        ];

                        $resultJson['total_final_shipping_cost'] += (float)$quoteItem->getFinalShippingCost();
                        $resultJson['total_provider_shipping_cost'] += (float)$quoteItem->getProviderShippingCost();

                        $resultJson['quotes'][] = $quotes;
                    }

                    $stored[$quoteItem->getQuoteId()] = $quoteItem->getQuoteId();
                }

                if ($resultJson) {
                    $order->setData('intelipost_quotes', $this->helper->serializeData($resultJson));
                    $this->setShipmentOrder($resultJson);
                }
            }
        } catch (\Exception $e) {
            $this->helper->getLogger()->error($e->getMessage());
        }
    }

    /**
     * @param $resultJson
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setShipmentOrder($resultJson)
    {
        $orderIndex = 1;
        $orderNumber = $resultJson['order_id'];

        foreach ($resultJson['quotes'] as $quotes) {
            $shipment = $this->shipmentFactory->create();
            $shipment->setOrderIncrementId($orderNumber);
            $shipment->setQuoteId($quotes['quote_id']);
            $shipment->setDeliveryMethodId($this->getMethodId($quotes['delivery_method_id']));
            $shipment->setDeliveryEstimateBusinessDays($quotes['delivery_estimated_delivery_business_day']);
            $shipment->setIntelipostShipmentId($orderNumber);
            if ($orderIndex != 1) {
                $shipment->setIntelipostShipmentId($orderNumber . '-' . $orderIndex);
            }
            $shipment->setShipmentOrderType('NORMAL');
            $shipment->setShipmentOrderSubType('NORMAL');
            $shipment->setDeliveryMethodType($quotes['delivery_method_type']);
            $shipment->setDeliveryMethodName($quotes['delivery_method_name']);
            $shipment->setDescription($quotes['description']);
            $shipment->setSalesChannel($this->storeManager->getStore()->getName());
            $shipment->setProviderShippingCosts($quotes['provider_shipping_cost']);
            $shipment->setCustomerShippingCosts($quotes['final_shipping_cost']);
            $shipment->setIntelipostStatus('pending');
            $shipment->setVolumes($this->helper->serializeData($quotes['quote_volume']));
            $shipment->setDeliveryEstimateDateExactIso($quotes['delivery_exact_estimated_date']);
            $shipment->setScheduled(false);
            $shipment->setProductsIds($this->helper->serializeData($this->setProductsArray($quotes['products'])));
            $shipment->setOriginZipCode($quotes['origin_zip_code']);

            if ($quotes['selected_scheduling_dates']) {
                $shipment->setScheduled(true);
                $shipment->setSelectedSchedulingDate($quotes['selected_scheduling_dates']);
            }

            $orderIndex++;

            $this->shipmentResource->save($shipment);
        }
    }

    /**
     * @param $methodId
     * @return int
     */
    public function getMethodId($methodId)
    {
        preg_match_all('!\d+!', $methodId, $matches);
        foreach ($matches as $value) {
            $id = ($value) ? (int)$value[0] : 100;
        }
        return $id;
    }

    /**
     * @param array $products
     * @return array
     */
    public function setProductsArray($products)
    {
        $productsArray = [];

        if (is_array($products) && !empty($products)) {
            foreach ($products as $prod) {
                $productsArray[] = $prod['id'];
            }
        }
        return $productsArray;
    }
}
