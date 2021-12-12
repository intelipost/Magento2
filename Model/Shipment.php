<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Model;

use Intelipost\Shipping\Api\Data\ShipmentInterface;
use Magento\Framework\Model\AbstractModel;
use Intelipost\Shipping\Model\ResourceModel\Shipment as ShipmentResource;

class Shipment extends AbstractModel implements ShipmentInterface
{
    CONST STATUS_PENDING = 'pending';
    CONST STATUS_ERROR = 'error';
    CONST STATUS_SHIPPED = 'shipped';
    CONST STATUS_CREATED = 'created';

    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'intelipost_shipments';

    /**
     * @var string
     */
    protected $_cacheTag = 'intelipost_shipments';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'intelipost_shipments';

    protected function _construct()
    {
        $this->_init(ShipmentResource::class);
    }
}
