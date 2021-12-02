<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Bizcommerce
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

        if($invoiceId) {
            try {
                $this->invoiceRepository->deleteById($invoiceId);

                $resultPageFactory = $this->layoutFactory->create();
                $html = $resultPageFactory->getLayout()->createBlock('Intelipost\Shipping\Block\Adminhtml\Order\View\Tab\Intelipost')->toHtml();

                $resultRaw = $this->rawFactory->create();
                $resultRaw->setContents($html);
                return $resultRaw;
            } catch (\Exception $e) {
                $this->helper->getLogger()->critical($e->getMessage());
            }
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['error' => true, 'message' => __('It was not possible to remove the item')]);
        return $resultJson;

    }
}
