<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Controller\Adminhtml\Invoices;

use Intelipost\Shipping\Model\NfeXmlParser;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Importxml extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Intelipost_Shipping::invoices';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var NfeXmlParser
     */
    protected $nfeXmlParser;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param NfeXmlParser $nfeXmlParser
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        NfeXmlParser $nfeXmlParser,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->nfeXmlParser = $nfeXmlParser;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        try {
            $xmlContent = $this->getRequest()->getParam('xml_content');
            $orderId = $this->getRequest()->getParam('order_id');
            
            // Trim the XML content to remove any leading/trailing whitespace
            if ($xmlContent) {
                $xmlContent = trim($xmlContent);
            }
            
            if (empty($xmlContent)) {
                return $result->setData([
                    'success' => false,
                    'message' => __('XML content is required.')
                ]);
            }

            // Validate and parse XML
            $this->nfeXmlParser->validateNfeXml($xmlContent);
            $nfeData = $this->nfeXmlParser->parseNfeXml($xmlContent);
            
            // Get order to use its increment ID if not found in XML
            if ($orderId && empty($nfeData['order_increment_id'])) {
                $order = $this->orderRepository->get($orderId);
                $nfeData['order_increment_id'] = $order->getIncrementId();
            }
            
            // Format date for display in the form (dd/mm/yyyy)
            if (!empty($nfeData['date'])) {
                $date = new \DateTime($nfeData['date']);
                $nfeData['date'] = $date->format('d/m/Y');
            }
            
            return $result->setData([
                'success' => true,
                'data' => $nfeData,
                'message' => __('NFe data extracted successfully.')
            ]);
            
        } catch (\Exception $e) {
            $this->logger->error('Error importing NFe XML in admin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $result->setData([
                'success' => false,
                'message' => __('Error parsing NFe XML: %1', $e->getMessage())
            ]);
        }
    }
}