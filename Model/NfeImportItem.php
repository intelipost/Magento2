<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\NfeImportItemInterface;
use Magento\Framework\DataObject;

class NfeImportItem extends DataObject implements NfeImportItemInterface
{
    /**
     * @inheritdoc
     */
    public function getXmlContent()
    {
        return $this->getData(self::XML_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setXmlContent($xmlContent)
    {
        return $this->setData(self::XML_CONTENT, $xmlContent);
    }

    /**
     * @inheritdoc
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }
}