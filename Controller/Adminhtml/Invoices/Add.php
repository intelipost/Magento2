<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Invoices;

use Intelipost\Shipping\Helper\Data;
use Intelipost\Shipping\Model\ResourceModel\InvoiceRepository;
use Intelipost\Shipping\Model\NfeXmlParser;
use Intelipost\Shipping\Client\InvoiceApi;
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

    /** @var NfeXmlParser */
    protected $nfeXmlParser;

    /** @var InvoiceApi */
    protected $invoiceApi;

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
     * @param NfeXmlParser $nfeXmlParser
     * @param InvoiceApi $invoiceApi
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
        Data $helper,
        NfeXmlParser $nfeXmlParser,
        InvoiceApi $invoiceApi
    ) {
        $this->invoiceFactory = $invoiceFactory;
        $this->invoiceRepository = $invoiceRepository;
        $this->helper = $helper;
        $this->nfeXmlParser = $nfeXmlParser;
        $this->invoiceApi = $invoiceApi;
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);
    }

    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_initOrder();

        if ($order) {
            try {
                $params = $this->getRequest()->getParams();

                // Check if NFe XML was provided
                if (isset($params['nfe_xml']) && !empty(trim($params['nfe_xml']))) {
                    try {
                        // Parse NFe XML to extract data (trim before parsing)
                        $nfeData = $this->nfeXmlParser->parseNfeXml(trim($params['nfe_xml']));

                        // Override form data with NFe data but keep any manually entered data that wasn't in XML
                        $params = array_merge($params, $nfeData);

                        // Use order increment ID if not found in XML
                        if (empty($params['order_increment_id'])) {
                            $params['order_increment_id'] = $order->getIncrementId();
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addWarningMessage(__('Could not parse NFe XML, using manual data: %1', $e->getMessage()));
                    }
                }

                $invoice = $this->invoiceFactory->create();
                $invoice->addData($params);

                if (isset($params['date']) && !empty(trim($params['date']))) {
                    // Try both date formats (from form dd/mm/yyyy or from XML yyyy-mm-dd)
                    $invoiceDate = \DateTime::createFromFormat('d/m/Y', $params['date']);
                    if (!$invoiceDate) {
                        $invoiceDate = \DateTime::createFromFormat('Y-m-d', $params['date']);
                    }
                    if (!$invoiceDate) {
                        $invoiceDate = new \DateTime($params['date']);
                    }
                    $invoice->setDate($invoiceDate->format('Y-m-d'));
                }

                //Doing this to mixed up url param 'key'
                if (isset($params['invoice_key'])) {
                    $invoice->setKey($params['invoice_key']);
                }

                $savedInvoice = $this->invoiceRepository->save($invoice);
                $this->messageManager->addSuccessMessage(__('Invoice successfully added!'));

                // Send invoice data to Intelipost API
                try {
                    $apiResult = $this->invoiceApi->sendInvoiceToIntelipost($savedInvoice);
                    if ($apiResult) {
                        $this->messageManager->addSuccessMessage(__('Invoice data sent to Intelipost successfully.'));
                    } else {
                        $this->messageManager->addWarningMessage(__('Invoice saved locally but could not be sent to Intelipost.'));
                    }
                } catch (\Exception $apiException) {
                    $this->messageManager->addWarningMessage(
                        __('Invoice saved locally but error sending to Intelipost: %1', $apiException->getMessage())
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('It wasn\'t possible to add the Invoice File: %1', $e->getMessage()));
            }
        } else {
            $this->messageManager->addErrorMessage(__('There was an error. Check the files and the information and try again.'));
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $order->getId()]);
    }

}
