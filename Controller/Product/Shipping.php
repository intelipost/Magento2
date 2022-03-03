<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Product;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class Shipping extends \Magento\Framework\App\Action\Action
{
    /** @var Quote */
    protected $quote;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Quote $quote
     * @param PageFactory $resultPageFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Quote $quote,
        PageFactory $resultPageFactory,
        ProductRepository $productRepository
    )
    {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;

        parent::__construct($context);
    }

    //@TODO improve it
    public function execute()
    {
        $rates = false;
        $template = 'Intelipost_Shipping::product/view/error.phtml';

        try {
            $country = $this->getRequest()->getParam('country');
            $postcode = $this->getRequest()->getParam('postcode');
            $productId = $this->getRequest()->getParam('product');
            $qty = $this->getRequest()->getParam('qty');

            $this->quote->getShippingAddress()
                ->setCountryId($country)
                ->setPostcode($postcode)
                ->setCollectShippingRates(true);

            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productRepository->getById($productId);

            $options = new \Magento\Framework\DataObject();
            $options->setProduct($product->getId());
            $options->setQty($qty);

            if (!strcmp($product->getTypeId(), 'configurable')) {
                $superAttribute = $this->getRequest()->getParam('super_attribute');
                $options->setSuperAttribute($superAttribute);
            } elseif (!strcmp($product->getTypeId(), 'bundle')) {
                $bundleOption = $this->getRequest()->getParam('bundle_option');
                $bundleOptionQty = $this->getRequest()->getParam('bundle_option_qty');

                $options->setBundleOption($bundleOption);
                $options->setBundleOptionQty($bundleOptionQty);
            }

            $this->quote->addProduct($product, $options);

            $this->quote->collectTotals();
            $rates = $this->quote->getShippingAddress()->getGroupedAllShippingRates();
            if (!is_string($rates)) {
                $template = 'Intelipost_Shipping::product/view/result.phtml';
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $resultPage = $this->resultPageFactory->create();
        $this->getResponse()->setBody(
            $resultPage->getLayout()
                ->createBlock(\Magento\Framework\View\Element\Template::class)
                ->setRates($rates)
                ->setTemplate($template)
                ->toHtml()
        );


    }
}
