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
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Intelipost\Shipping\Model\InvoiceFactory;

class Add extends \Magento\Sales\Controller\Adminhtml\Order implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Intelipost_Shipping::invoices';

    /** @var InvoiceFactory */
    protected $invoiceFactory;

    /** @var Data */
    protected $helper;

    /** @var InvoiceRepository */
    protected $invoiceRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param InvoiceFactory $invoiceFactory
     * @param InvoiceRepository $invoiceRepository
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        InvoiceFactory $invoiceFactory,
        InvoiceRepository $invoiceRepository,
        Data $helper
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->helper = $helper;
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);
    }

    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_initOrder();

        if ($order) {
            try {
                $params = $this->getRequest()->getParams();

                $invoice = $this->invoiceFactory->create();
                $invoice->addData($params);

                if (isset($params['date']) && !empty(trim($params['date']))) {
                    $invoiceDate = \DateTime::createFromFormat('d/m/Y', $params['date']);
                    $invoice->setDate($invoiceDate->format('Y-m-d'));
                }

                //Doing this to mixed up url param 'key'
                if (isset($params['invoice_key'])) {
                    $invoice->setKey($params['invoice_key']);
                }

                $this->invoiceRepository->save($invoice);
                $this->messageManager->addSuccessMessage(__('Invoice successfully added!'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('It wasn\'t possible to add the Invoice File.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('There was an error. Check the files and the information and try again.'));
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $order->getId()]);
    }

}
