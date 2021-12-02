<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

namespace Intelipost\Shipping\Controller\Calendar;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Intelipost\Shipping\Helper\Data */
    protected $helper;

    /** @var \Intelipost\Shipping\Model\QuoteFactory */
    protected $shippingFactory;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Intelipost\Shipping\Helper\Data $helper
     * @param \Intelipost\Shipping\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context      $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Intelipost\Shipping\Helper\Data           $helper,
        \Intelipost\Shipping\Model\QuoteFactory    $quoteFactory
    )
    {
        $this->helper = $helper;
        $this->shippingFactory = $quoteFactory;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $info = $this->getRequest()->getParam('info');
            if (empty($info)) {
                return false;
            }

            $pieces = explode('_', $info); // carrier, method, store id
            $pieces = $pieces ?: [];

            if (count($pieces) != 3) {
                return false;
            }

            $carrierName = $pieces [0];
            $deliveryMethodId = $pieces [1] . '_' . $pieces [2];

            $item = null;

            foreach ($this->helper->getResultQuotes() as $quote) {
                if (!strcmp($quote->getCarrier(), $carrierName)
                    && !strcmp($quote->getDeliveryMethodId(), $deliveryMethodId)
                ) {
                    $item = $quote;
                    break;
                }
            }

            if (empty($item)) {
                return false;
            }

            if (empty($item->getAvailableSchedulingDates())) {
                return false;
            }

            $resultPage = $this->resultPageFactory->create();

            return $this->getResponse()->setBody(
                $resultPage->getLayout()
                    ->createBlock(\Magento\Framework\View\Element\Template::class)
                    ->setQuoteItem($item)
                    ->setTemplate('Intelipost_Shipping::calendar/result.phtml')
                    ->toHtml()
            );
        } catch (\Exception $e) {

        }
        return false;
    }
}
