<?php

/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Helper;

use Intelipost\Shipping\Client\Intelipost;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Api
{
    const POST = 'POST';
    const GET = 'GET';

    const QUOTE_BY_PRODUCT = 'quote_by_product/';
    const QUOTE_PICKUP_BY_PRODUCT = 'quote/pickup_quote_by_product';
    const QUOTE_BUSINESS_DAYS = 'quote/business_days/';
    const QUOTE_AVAILABLE_SCHEDULING_DATES = 'quote/available_scheduling_dates/';

    /** @var ScopeConfigInterface  */
    protected $scopeConfig;

    /** @var Intelipost  */
    protected $client;

    /** @var Json */
    protected $json;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Intelipost $client
     * @param Json $json
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Intelipost $client,
        Json $json
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->client = $client;
        $this->json = $json;
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
        $response = $this->client->apiRequest($httpMethod, $apiMethod, $postData);
        $result = $this->json->unserialize($response);

        if (!strcmp($result ['status'], 'ERROR')) {
            throw new \Exception("Erro ao consultar API");
        }

        $postData['api_response'] = $response;

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
            Intelipost::GET,
            self::QUOTE_BUSINESS_DAYS . "{$originZipcode}/{$destPostcode}/{$businessDays}"
        );

        return $this->json->unserialize($response);
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
            Intelipost::GET,
            self::QUOTE_AVAILABLE_SCHEDULING_DATES . "{$deliveryMethodId}/{$originZipcode}/{$destPostcode}"
        );

        return $this->json->unserialize($response);
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
