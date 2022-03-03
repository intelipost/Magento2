<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Schedule;

class Status extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\Stdlib\CookieManagerInterface */
    protected $cookieManager;

    /** @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /** @var \Intelipost\Shipping\Model\QuoteFactory */
    protected $shippingFactory;

    /** @var \Intelipost\Shipping\Helper\Data */
    protected $shippingHelper;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Intelipost\Shipping\Model\QuoteFactory $quoteFactory
     * @param \Intelipost\Shipping\Helper\Data $quoteHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context            $context,
        \Magento\Framework\View\Result\PageFactory       $resultPageFactory,
        \Intelipost\Shipping\Model\QuoteFactory          $quoteFactory,
        \Intelipost\Shipping\Helper\Data                 $quoteHelper,
        \Magento\Checkout\Model\Session                  $checkoutSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    )
    {
        $this->shippingFactory = $quoteFactory;
        $this->shippingHelper = $quoteHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;

        parent::__construct($context);
    }

    public function execute()
    {
        $session = $this->checkoutSession;
        $quote = $session->getQuote();
        $address = $quote->getShippingAddress();
        $shippingMethod = $address->getShippingMethod();

        if ($session->getIpSelDate()) {
            if (strpos($shippingMethod, '_') !== false) {
                $methodId = explode('_', $shippingMethod);

                $id = $methodId[1] . '_' . $methodId[2];
                $scheduledId = $session->getIpScheludedMethodId();

                if ($scheduledId == $id) {
                    $resultQuotes = $this->shippingHelper->getResultQuotes();

                    if (!empty($resultQuotes) && count($resultQuotes) > 0 /* $collection->count() */) {
                        $cookie = $this->cookieManager->getCookie(
                            \Intelipost\Shipping\Controller\Schedule\Index::COOKIE_NAME
                        );
                        if ($cookie) {
                            $scheduled = explode('+', $cookie);

                            if ($scheduled[0] == $id) {
                                $selDate = $scheduled[1];
                                $period = $scheduled[2];
                            } else {
                                return null;
                            }
                        } else {
                            return null;
                        }
                    } else {
                        $selDate = $session->getIpSelDate();
                        $period = $session->getIpPeriod();

                        $item = null;

                        foreach ($resultQuotes as $quote) {
                            if (!strcmp($quote->getDeliveryMethodId(), $id)) {
                                $item = $quote;

                                break;
                            }
                        }

                        if (empty($item)) {
                            return null;
                        }

                        $item->setSelectedSchedulingDates($selDate);
                        $item->setSelectedSchedulingPeriod($period);
                    }

                    $this->getResponse()->setBody(
                        __('Delivery Scheduled for: %1 period: %2', $selDate, __(ucfirst($period)))
                    );
                }
            }
        }
    }
}
