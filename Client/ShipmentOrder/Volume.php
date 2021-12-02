<?php

namespace Intelipost\Shipping\Client\ShipmentOrder;

class Volume
{
    const TYPE_CODE = 'BOX';
    const PRODUCTS_NATURE = 'products';
    const IS_ICMS_EXEMPT = false;

    /**
     * @param $volumes
     * @param $invoice
     * @return array
     */
    public function getInformation($volumes, $invoice)
    {
        $vNumber = 1;
        $vol = json_decode($volumes);
        $volumes = [];
        foreach ($vol as $v) {
            $volumes[] = $this->getVolume($vNumber, $v, $invoice);
            $vNumber++;
        }
        return $volumes;
    }

    /**
     * @param $vNumber
     * @param $v
     * @param $invoice
     * @return \stdClass
     */
    public function getVolume($vNumber, $v, $invoice)
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

        if (!empty($invoice)) {
            $volume->shipment_order_volume_invoice = $invoice;
        }

        return $volume;
    }
}
