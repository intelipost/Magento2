<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Bizcommerce
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Ui\Component\Listing\Column;

use Intelipost\Shipping\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Backend\Model\UrlInterface;

class OrderId extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['order_increment_id']) {
                    $orderId = $item['order_entity_id'] ?? null;
                    if (!$orderId) {
                        $order = $this->helper->loadOrder($item['order_increment_id']);
                        $orderId = $order->getId();
                    }
                    $item[$fieldName] = sprintf(
                        '<a href="%s">%s</a>',
                        $this->getViewLink($orderId),
                        $item['order_increment_id']
                    );
                } else {
                    $item[$fieldName] = __('Not Available');
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param $entityId
     * @return string
     */
    protected function getViewLink($entityId)
    {
        return $this->urlBuilder->getUrl(
            'sales/order/view',
            ['order_id' => $entityId]
        );
    }
}
