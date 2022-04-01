<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Helper\Data;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class Intelipost
{
    const POST = 'POST';
    const GET = 'GET';
    const RESPONSE_STATUS_OK = 'OK';
    const RESPONSE_STATUS_ERROR = 'ERROR';

    const DEAFULT_TIMEOUT = 30;
    const DEAFULT_PLATFORM = 'Magento2.3+';

    /** @var LoggerInterface */
    protected $logger;

    /** @var Data */
    protected $helper;

    /**
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param $httpMethod
     * @param $apiMethod
     * @param bool|string $postData
     * @return bool|string
     */
    public function apiRequest($httpMethod, $apiMethod, $postData = false)
    {
        $responseBody = false;

        try {
            if (!empty($postData) && !is_string($postData)) {
                $postData = $this->helper->serializeData($postData);
            }

            $apiUrl = $this->helper->getConfig('api_url', 'settings', 'intelipost_basic');
            $apiKey = $this->helper->getConfig('api_key', 'settings', 'intelipost_basic');
            $client = new Client([
                'base_uri' => $apiUrl,
                'timeout' => self::DEAFULT_TIMEOUT,
            ]);

            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api_key' => $apiKey,
                    'platform' => $this->getPlatform()
                ]
            ];
            if ($httpMethod == self::POST && $postData) {
                $options['body'] = $postData;
            }

            $response = $client->request($httpMethod, $apiMethod, $options);
            $responseBody = $response->getBody()->getContents();

            if (!$response) {
                $this->logger->error(__('Error sending data to Intelipost'));
                $responseBody = false;
            }

            $this->handleResponse($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $responseBody;
    }

    /**
     * Making it as a separated public method allows to change it with plugin if necessary
     * @return string
     */
    public function getPlatform()
    {
        return self::DEAFULT_PLATFORM;
    }

    /**
     * @param $response
     * @return boolean
     */
    protected function handleResponse($response)
    {
        try {
            $objResponse = $this->helper->unserializeData($response);
            if (!$objResponse) {
                $this->logger->error(__('ERROR - API didn\'t worked'));
                return false;
            }

            if ($objResponse['status'] != self::RESPONSE_STATUS_OK) {
                $this->logger->error(__('ERROR - (%1) %2', $objResponse['messages'][0]['key'], $objResponse['messages'][0]['text']));
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        $this->helper->log(__('API Response: %1', $response));
        return true;
    }
}
