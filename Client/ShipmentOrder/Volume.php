<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client\ShipmentOrder;

class Volume
{
    const TYPE_CODE = 'BOX';
    const PRODUCTS_NATURE = 'products';
    const IS_ICMS_EXEMPT = false;

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

        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            $productData = [
                'weight' => (float) $item->getWeight() ?: 0.1,
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
        }

        return $products;
    }

    /**
     * Get product dimension attribute value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $dimension
     * @return float
     */
    private function getProductDimension($product, $dimension)
    {
        $value = 0;

        try {
            $attributeValue = $product->getData($dimension);
            if ($attributeValue) {
                $value = (float) $attributeValue;
            }
        } catch (\Exception $e) {
            $value = 0;
        }

        return $value ?: 1;
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
