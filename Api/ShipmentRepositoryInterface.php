<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Bizcommerce
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace Intelipost\Shipping\Api;

use Intelipost\Shipping\Api\Data\ShipmentInterface;
use Intelipost\Shipping\Api\Data\ShipmentSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ShipmentRepositoryInterface
{

    /**
     * Save Queue
     * @param ShipmentInterface $shipment
     * @return ShipmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        ShipmentInterface $shipment
    );

    /**
     * Retrieve Queue
     * @param string $id
     * @return ShipmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return ShipmentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Queue
     * @param ShipmentInterface $shipment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        ShipmentInterface $shipment
    );

    /**
     * Delete Queue by ID
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}

