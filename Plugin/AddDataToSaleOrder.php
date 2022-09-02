<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2022 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace Intelipost\Shipping\Plugin;

use Intelipost\Shipping\Helper\Data;
use Magento\Sales\Api\OrderRepositoryInterface;

class AddDataToSaleOrder
{
    /** @var Data  */
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGet(OrderRepositoryInterface $subject, $result)
    {
        try {
            $extensionAttributes = $result->getExtensionAttributes();

            $quoteIds = [];
            $quoteData = $this->helper->unserializeData($result->getData('intelipost_quotes'));
            if (isset($quoteData['quotes']) && is_array($quoteData['quotes'])) {
                foreach ($quoteData['quotes'] as $quote) {
                    $quoteIds[] = $quote['quote_id'];
                }
            }
            $extensionAttributes->setData('intelipost_quote_id', implode(',', $quoteIds));

            $result->setExtensionAttributes($extensionAttributes);
        } catch (\Exception $e) {
            $this->helper->getLogger()->error($e->getMessage());
        }

        return $result;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param $result
     * @return mixed
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        $result
    ) {
        foreach ($result->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $result;
    }
}
