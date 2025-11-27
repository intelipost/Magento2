<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client\ShipmentOrder;

use Intelipost\Shipping\Helper\Data;
use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory;

class Customer
{
    /** @var CollectionFactory  */
    protected $addressCollectionFactory;

    /** @var Data  */
    protected $helperData;

    public function __construct(
        CollectionFactory $addressCollectionFactory,
        Data $helperData
    ) {
        $this->addressCollectionFactory = $addressCollectionFactory;
        $this->helperData = $helperData;
    }

    public function getInformation($orderIncrementId, $taxVat): \stdClass
    {
        $collection = $this->getAddressCollection($orderIncrementId);

        $customer = new \stdClass();
        /** @var \Magento\Sales\Model\Order\Address $addressModel */
        foreach ($collection as $addressModel) {
            $customer->first_name = $addressModel->getData('firstname');
            $customer->last_name = $addressModel->getData('lastname');
            $customer->email = $addressModel->getData('email');
            $customer->phone = $addressModel->getData('telephone');
            $customer->cellphone = $addressModel->getData('telephone');
            $customer->is_company = false;
            $customer->federal_tax_payer_id = $taxVat;
            $customer->shipping_address = $addressModel->getStreetLine($this->helperData->getStreetAttribute());
            $customer->shipping_number = $addressModel->getStreetLine($this->helperData->getNumberAttribute())
                ?: $this->getAddressNumber($addressModel);
            $customer->shipping_additional = $addressModel->getStreetLine($this->helperData->getComplementAttribute());
            $customer->shipping_quarter = $addressModel->getStreetLine($this->helperData->getDistrictAttribute());
            $customer->shipping_city = $addressModel->getData('city');
            $customer->shipping_state = $addressModel->getData('region');
            $customer->shipping_zip_code = $addressModel->getData('postcode');
            $customer->shipping_country = $addressModel->getData('country_id');
        }
        return $customer;
    }

    /**
     * @param $orderIncrementId
     * @return \Magento\Sales\Model\ResourceModel\Order\Address\Collection
     */
    public function getAddressCollection($orderIncrementId)
    {
        $collection = $this->addressCollectionFactory->create();
        $collection->addFieldToFilter('parent_id', $orderIncrementId)
            ->addFieldToFilter('address_type', 'shipping');
        return $collection;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address $addressModel
     * @return string
     */
    public function getAddressNumber($addressModel): string
    {
        $number = trim((string) $addressModel->getStreetLine($this->helperData->getStreetAttribute()));
        $shippingNumber = "s/n";
        if ($number) {
            $number = explode(',', $number);
            if (isset($number[1]) && is_numeric(trim($number[1]))) {
                $shippingNumber = trim($number[1]);
            }
        }
        return $shippingNumber;
    }
}
