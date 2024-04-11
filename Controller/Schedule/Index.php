<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Schedule;

class Index extends \Magento\Framework\App\Action\Action
{
    const COOKIE_NAME = 'scheduled_option';
    const COOKIE_DURATION = 1800; // lifetime in seconds

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /** @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory */
    protected $cookieMetadataFactory;

    /** @var \Intelipost\Shipping\Helper\Data */
    protected $helper;

    /** @var \Magento\Checkout\Model\Session */
    protected $checkoutSession;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Intelipost\Shipping\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $session = $this->checkoutSession;

        $quoteId = $this->getRequest()->getParam('quoteId');
        $methodId = $this->getRequest()->getParam('methodId');
        $selDate = $this->getRequest()->getParam('selDate');
        $period = $this->getRequest()->getParam('period');

        // Check
        if (empty($quoteId) || empty($methodId) || empty($selDate) || empty($period)) {
            return false;
        }

        if (((int)$quoteId) < 1 || !strtotime($selDate)) {
            return false;
        }

        $quoteItem = null;

        foreach ($this->helper->getResultQuotes() as $quote) {
            if ($quote->getQuoteId() == $quoteId && !strcmp($quote->getDeliveryMethodId(), $methodId)) {
                $quoteItem = $quote;
                break;
            }
        }

        if (empty($quoteItem)) {
            return null;
        }

        // save
        $timestamp = strtotime($selDate);
        $selDate = date('d/m/Y', $timestamp);
        $quoteItem->setSelectedSchedulingDates($selDate);
        $quoteItem->setSelectedSchedulingPeriod($period);

        $session->setIpSelDate($selDate);
        $session->setIpPeriod($period);
        $session->setIpScheludedMethodId($quoteItem->getDeliveryMethodId());

        if ($this->cookieManager->getCookie(self::COOKIE_NAME)) {
            $this->cookieManager->deleteCookie(self::COOKIE_NAME);
        }

        $cookie_values = $quoteItem->getDeliveryMethodId() . '+' . $selDate . '+' . $period;

        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION)
            ->setPath('/');

        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $cookie_values,
            $metadata
        );

        return $this->getResponse()->setBody(
            __('Delivery Scheduled for: %1 period: %2', $selDate, __(ucfirst($period)))
        );
    }
}
