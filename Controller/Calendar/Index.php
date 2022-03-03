<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Calendar;

use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\QuoteFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /** @var Data */
    protected $helper;

    /** @var QuoteFactory */
    protected $shippingFactory;

    /** @var PageFactory */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helper
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        Context      $context,
        PageFactory $resultPageFactory,
        Data           $helper,
        QuoteFactory    $quoteFactory
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
