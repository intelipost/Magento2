<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client\ShipmentOrder;

use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory;

class Customer
{
    /** @var CollectionFactory  */
    protected $addressCollectionFactory;

    public function __construct(CollectionFactory $addressCollectionFactory)
    {
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    public function getInformation($orderIncrementId, $taxVat)
    {
        $collection = $this->getAddressCollection($orderIncrementId);

        $customer = new \stdClass();
        /** @var \Magento\Sales\Model\Order\Address $addressModel */
        foreach ($collection as $addressModel) {
            $customer->first_name = $addressModel->getData('firstname');
            $customer->last_name = $addressModel->getData('lastname');
            $customer->email = $addressModel->getData('email');
            $customer->phone = $addressModel->getData('telephone');
            $customer->is_company = false;
            $customer->federal_tax_payer_id = $taxVat;
            $customer->shipping_address = $addressModel->getStreetLine(1);
            $customer->shipping_number = $addressModel->getStreetLine(2) ?: $this->getAddressNumber($addressModel);
            $customer->shipping_additional = $addressModel->getStreetLine(3);
            $customer->shipping_quarter = $addressModel->getStreetLine(4);
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
    public function getAddressNumber($addressModel)
    {
        $number = trim((string) $addressModel->getStreetLine(1));
        $shippingNumber = "s/n";
        if ($number) {
            $number = explode(',', $number);
            if (is_numeric(trim($number[1]))) {
                $shippingNumber = trim($number[1]);
            }
        }
        return $shippingNumber;
    }
}
