<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Bizcommerce
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace Intelipost\Shipping\Api;

use Intelipost\Shipping\Api\Data\QuoteInterface;
use Intelipost\Shipping\Api\Data\QuoteSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QuoteRepositoryInterface
{

    /**
     * Save Queue
     * @param QuoteInterface $quote
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        QuoteInterface $quote
    );

    /**
     * Retrieve Queue
     * @param string $id
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return QuoteSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Queue
     * @param QuoteInterface $quote
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        QuoteInterface $quote
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

