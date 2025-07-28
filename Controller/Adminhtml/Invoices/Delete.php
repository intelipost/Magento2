<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Invoices;

use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\InvoiceRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\LayoutFactory;

class Delete extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Intelipost_Shipping::invoices';

    /** @var JsonFactory */
    protected $resultJsonFactory;

    /** @var Data */
    protected $helper;

    /** @var LayoutFactory */
    protected $layoutFactory;

    /** @var RawFactory */
    protected $rawFactory;

    /** @var InvoiceRepository */
    protected $invoiceRepository;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param RawFactory $rawFactory
     * @param LayoutFactory $layoutFactory
     * @param Data $helper
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        RawFactory $rawFactory,
        LayoutFactory $layoutFactory,
        Data $helper,
        InvoiceRepository $invoiceRepository
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->rawFactory = $rawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->helper = $helper;
        $this->invoiceRepository = $invoiceRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $resultJson = $this->resultJsonFactory->create();

        if($invoiceId) {
            try {
                $this->invoiceRepository->deleteById($invoiceId);
                
                // Return success response that will trigger a page reload
                $resultJson->setData([
                    'success' => true, 
                    'message' => __('Invoice deleted successfully.'),
                    'reload' => true
                ]);
                return $resultJson;
            } catch (\Exception $e) {
                $this->helper->getLogger()->critical($e->getMessage());
                $resultJson->setData([
                    'error' => true, 
                    'message' => __('Error deleting invoice: %1', $e->getMessage())
                ]);
                return $resultJson;
            }
        }

        $resultJson->setData(['error' => true, 'message' => __('Invoice ID not provided.')]);
        return $resultJson;
    }
}
