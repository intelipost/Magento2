<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Client;

use Intelipost\Shipping\Helper\Data;
use Psr\Log\LoggerInterface;

class Intelipost
{
    const POST = 'POST';
    const GET = 'GET';
    const DEAFULT_TIMEOUT = 30;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Data */
    protected $helper;

    /**
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data            $helper,
        LoggerInterface $logger
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param $httpMethod
     * @param $apiMethod
     * @param bool|string $encPostData
     * @return bool|string
     */
    public function apiRequest($httpMethod, $apiMethod, $encPostData = false)
    {
        $response = false;

        try {
            if (!empty($encPostData) && !is_string($encPostData)) {
                $encPostData = json_encode($encPostData);
            }

            $apiUrl = $this->helper->getConfig('api_url', 'settings', 'intelipost_basic');
            $apiKey = $this->helper->getConfig('api_key', 'settings', 'intelipost_basic');
            $headers = ['Content-Type: application/json', "api_key: {$apiKey}", "platform: Magento2.3+"];

            $curl = curl_init();
            if (!$curl) {
                $this->logger->error("Erro ao tentar iniciar o cURL");
                return $response;
            }

            curl_setopt($curl, CURLOPT_TIMEOUT, self::DEAFULT_TIMEOUT);
            curl_setopt($curl, CURLOPT_URL, $apiUrl . $apiMethod);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_VERBOSE, true);

            if ($httpMethod === self::POST && $encPostData) {
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $encPostData);
            }

            $response = curl_exec($curl);
            curl_close($curl);

            if (!$response) {
                $this->logger->error("Erro ao consultar a API da Intelipost");
                return $response;
            }

            $this->handleResponse($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $response;
    }

    /**
     * @param $response
     */
    protected function handleResponse($response)
    {
        $objResponse = json_decode($response);
        if (!$objResponse) {
            $this->logger->error("ERROR - API não respondeu");
            return;
        }
        if ($objResponse->status != "OK") {
            $this->logger->error("ERROR - (" . $objResponse->messages[0]->key . ") " . $objResponse->messages[0]->text);
            return;
        }

        $this->logger->info("Informações recebidas da API :" . $response);
    }
}
