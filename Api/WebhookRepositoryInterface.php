<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Api;

use Intelipost\Shipping\Api\Data\WebhookInterface;
use Intelipost\Shipping\Api\Data\WebhookSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface WebhookRepositoryInterface
{
    /**
     * Save Queue
     * @param WebhookInterface $invoice
     * @return WebhookInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        WebhookInterface $invoice
    );

    /**
     * Retrieve Queue
     * @param string $id
     * @return WebhookInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return WebhookSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Queue
     * @param WebhookInterface $invoice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        WebhookInterface $invoice
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
