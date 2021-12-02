<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

namespace Intelipost\Shipping\Controller\Product;

class Shipping extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Quote\Model\Quote */
    protected $quote;

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /** @var \Magento\Catalog\Model\ProductRepository */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context      $context,
        \Magento\Quote\Model\Quote                 $quote,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ProductRepository   $productRepository
    )
    {
        $this->quote = $quote;
        $this->resultPageFactory = $resultPageFactory;
        $this->productRepository = $productRepository;

        parent::__construct($context);
    }

    //@TOTO improve it
    public function execute()
    {
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
            $result = $this->quote->getShippingAddress()->getGroupedAllShippingRates();
            if (is_string($result)) {
                var_dump($result);
                die();
            }

            $resultPage = $this->resultPageFactory->create();
            $this->getResponse()->setBody(
                $resultPage->getLayout()
                    ->createBlock(\Magento\Framework\View\Element\Template::class)
                    ->setRates($result)
                    ->setTemplate('Intelipost_Shipping::product/view/result.phtml')
                    ->toHtml()
            );
        } catch (\Exception $e) {

        }


    }
}
