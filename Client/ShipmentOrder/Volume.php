<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client\ShipmentOrder;

use Intelipost\Shipping\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Volume
{
    const TYPE_CODE = 'BOX';
    const PRODUCTS_NATURE = 'products';
    const IS_ICMS_EXEMPT = false;

    /** @var Data */
    protected $helper;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /**
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $volumes
     * @param $invoice
     * @param $order
     * @return array
     */
    public function getInformation($volumes, $invoice, $order = null)
    {
        $vNumber = 1;
        $vol = json_decode($volumes);
        $volumes = [];
        foreach ($vol as $v) {
            $volumes[] = $this->getVolume($vNumber, $v, $invoice, $order);
            $vNumber++;
        }
        return $volumes;
    }

    /**
     * @param $vNumber
     * @param $v
     * @param $invoice
     * @param $order
     * @return \stdClass
     */
    public function getVolume($vNumber, $v, $invoice, $order = null)
    {
        $volume = new \stdClass();
        $volume->shipment_order_volume_number = $vNumber;
        $volume->volume_type_code = self::TYPE_CODE;
        $volume->weight = $v->weight;
        $volume->height = $v->height;
        $volume->length = $v->length;
        $volume->width = $v->width;
        $volume->products_quantity = $v->products_quantity;
        $volume->products_nature = self::PRODUCTS_NATURE;
        $volume->is_icms_exempt = self::IS_ICMS_EXEMPT;

        if (!empty((array) $invoice)) {
            $volume->shipment_order_volume_invoice = $invoice;
        }

        if ($order) {
            $volume->products = $this->getProductsData($order);
        }

        return $volume;
    }

    /**
     * Extract products data from order items for Intelipost API
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function getProductsData($order)
    {
        $products = [];
        $weightUnit = $this->getConfigData('weight_unit') == 'gr' ? 1000 : 1;
        $defaultWeight = intval($this->getConfigData('default_weight')) / $weightUnit;

        foreach ($order->getAllVisibleItems() as $item) {
            try {
                // Get full product data from repository to ensure we have all attributes
                $product = $this->productRepository->getById($item->getProductId());

                // Calculate weight using same logic as Carrier class
                $itemWeight = $item->getWeight() / $weightUnit;
                $finalWeight = $this->helper->haveData($itemWeight, $defaultWeight, 0.1);

                $productData = [
                    'weight' => (float) $finalWeight,
                    'width' => $this->getProductDimension($product, 'width'),
                    'height' => $this->getProductDimension($product, 'height'),
                    'length' => $this->getProductDimension($product, 'length'),
                    'price' => (float) $item->getPrice(),
                    'description' => $this->truncateString($product->getName(), 250),
                    'sku' => $item->getSku(),
                    'quantity' => (int) $item->getQtyOrdered()
                ];

                $imageUrl = $this->getProductImageUrl($product);
                if ($imageUrl) {
                    $productData['image_url'] = $this->truncateString($imageUrl, 250);
                }

                $products[] = $productData;
            } catch (\Exception $e) {
                // Skip product if there's an error, but continue with others
                continue;
            }
        }

        return $products;
    }

    /**
     * Get product dimension attribute value using existing configuration system
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $dimension (height, width, length)
     * @return float
     */
    private function getProductDimension($product, $dimension)
    {
        // Get configured attribute name for this dimension
        $attributeName = $this->getConfigData($dimension . '_attribute');
        $defaultValue = $this->getConfigData('default_' . $dimension);

        $value = 0;

        try {
            if ($attributeName) {
                $value = $product->getData($attributeName);
            }
        } catch (\Exception $e) {
            $value = 0;
        }

        // Use helper's haveData to handle fallback logic
        return $this->helper->haveData($value, $defaultValue, 1);
    }

    /**
     * Get configuration data for shipping carrier
     *
     * @param string $field
     * @return mixed
     */
    private function getConfigData($field)
    {
        return $this->scopeConfig->getValue(
            'carriers/intelipost/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get product image URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string|null
     */
    private function getProductImageUrl($product)
    {
        try {
            $imageUrl = $product->getImageUrl();
            return $imageUrl ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Truncate string to specified length
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    private function truncateString($string, $length)
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length - 3) . '...';
    }
}
