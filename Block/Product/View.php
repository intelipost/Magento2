<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

namespace Intelipost\Shipping\Block\Product;

use Intelipost\Shipping\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;


class View extends \Magento\Catalog\Block\Product\View
{
    protected $helper;

    /**
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $intelipostHelper
     * @param array $data
     */
    public function __construct(
        Context                                  $context,
        EncoderInterface                         $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils                              $string,
        Product                                  $productHelper,
        ConfigInterface                          $productTypeConfig,
        FormatInterface                          $localeFormat,
        Session                                  $customerSession,
        ProductRepositoryInterface               $productRepository,
        PriceCurrencyInterface                   $priceCurrency,
        Data                                     $helper,
        array                                    $data = []
    )
    {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
    }

    /**
     * @return string
     */
    public function getAjaxShippingUrl()
    {
        return $this->getUrl('intelipost/product/shipping');
    }

    /**
     * @return \Magento\Catalog\Model\Product|mixed|null
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('current_product');
        return $product;
    }

    /**
     * @return string
     */
    public function getCurrentProductUrl()
    {
        return $this->helper->getCurrentUrl();
    }
}
