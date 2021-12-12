<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Helper;

class Api
{
    const POST = 'POST';
    const GET = 'GET';

    const QUOTE_BY_PRODUCT = 'quote_by_product/';
    const QUOTE_BUSINESS_DAYS = 'quote/business_days/';
    const QUOTE_AVAILABLE_SCHEDULING_DATES = 'quote/available_scheduling_dates/';

    protected $scopeConfig;
    protected $client;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Intelipost\Shipping\Client\Intelipost $client
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Intelipost\Shipping\Client\Intelipost             $client
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->client = $client;
    }

    /**
     * @param $httpMethod
     * @param $apiMethod
     * @param false $postData
     * @return array[]|mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function quoteRequest($httpMethod, $apiMethod, &$postData = false)
    {
        $postData ['api_request'] = $postData;

        $response = $this->client->apiRequest($httpMethod, $apiMethod, $postData);
        $result = json_decode($response, true);

        if (!strcmp($result ['status'], 'ERROR')) {
            throw new \Exception("Erro ao consultar API");
        }

        $postData ['api_response'] = $response;

        return $result;
    }

    /**
     * @param $postData
     * @return string
     */
    public function getCacheIdentifier($postData)
    {
        $identifier = 'intelipost_api_'
            . $postData ['destination_zip_code'] . '_'
            . $postData ['cart_weight'] . '_'
            . $postData ['cart_amount'] . '_'
            . $postData ['cart_qtys'];

        return $identifier;
    }

    /**
     * @param $originZipcode
     * @param $destPostcode
     * @param $businessDays
     * @return mixed
     */
    public function getEstimateDeliveryDate($originZipcode, $destPostcode, $businessDays)
    {
        $response = $this->apiRequest(
            \Intelipost\Shipping\Client\Intelipost::GET,
            self::QUOTE_BUSINESS_DAYS . "{$originZipcode}/{$destPostcode}/{$businessDays}"
        );

        return json_decode($response, true);
    }

    /**
     * @param $originZipcode
     * @param $destPostcode
     * @param $deliveryMethodId
     * @return mixed
     */
    public function getAvailableSchedulingDates($originZipcode, $destPostcode, $deliveryMethodId)
    {
        $response = $this->apiRequest(
            \Intelipost\Shipping\Client\Intelipost::GET,
            self::QUOTE_AVAILABLE_SCHEDULING_DATES . "{$deliveryMethodId}/{$originZipcode}/{$destPostcode}"
        );

        return json_decode($response, true);
    }

    /**
     * @param $httpMethod
     * @param $apiMethod
     * @param false $encPostData
     * @return bool|string|null
     */
    public function apiRequest($httpMethod, $apiMethod, $encPostData = false)
    {
        return $this->client->apiRequest($httpMethod, $apiMethod, $encPostData);
    }
}
