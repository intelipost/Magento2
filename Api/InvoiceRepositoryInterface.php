<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) Intelipost
 * @author      Alex Restani <alex.restani@intelipost.com.br>
 */

namespace Intelipost\Shipping\Api;

use Intelipost\Shipping\Api\Data\InvoiceInterface;
use Intelipost\Shipping\Api\Data\InvoiceSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface InvoiceRepositoryInterface
{
    /**
     * Save item information
     *
     * @param \Intelipost\Shipping\Api\Data\InvoiceInterface[] $invoice
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function saveInvoices($invoice);

    /**
     * Save Queue
     * @param InvoiceInterface $invoice
     * @return InvoiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        InvoiceInterface $invoice
    );

    /**
     * Retrieve Queue
     * @param string $id
     * @return InvoiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Queue matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return InvoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Queue
     * @param InvoiceInterface $invoice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        InvoiceInterface $invoice
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
