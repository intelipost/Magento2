<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Plugin\Quote;

class Address
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /** @var \Psr\Log\LoggerInterface */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface                           $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /*
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetGroupedAllShippingRates($subject, $result)
    {
        try {
            $scheduledIndex = 0;
            $scheduled = null;

            $agTitle = $this->scopeConfig->getValue("carriers/intelipost/scheduled_title");
            $agLast = $this->scopeConfig->getValue("carriers/intelipost/scheduled_last");

            if (!$agLast) {
                return $result;
            }

            foreach ($result as $value) {
                foreach ($value as $c => $v) {
                    if ($v->getMethodTitle() == $agTitle) {
                        $scheduled = $v;
                        $scheduledIndex = $c ? $c : 0;
                    }
                }
            }

            if ($scheduled) {
                unset($result['intelipost'][$scheduledIndex]);
                $result['intelipost'][] = $scheduled;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
