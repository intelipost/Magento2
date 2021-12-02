<?php

namespace Intelipost\Shipping\Client;

use Magento\Framework\Model\AbstractModel;

class Shipped extends AbstractModel
{
    public $shipArray = array();
    public $order_number;
    public $event_date;

    public $message;

    protected $_helper;
    protected $_helperApi;
    protected $_date;
    protected $_timezone;
    protected $_shipment;

    /**
     * @param \Intelipost\Shipping\Helper\Api $helperApi
     * @param \Intelipost\Shipping\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Intelipost\Shipping\Model\Shipment $shipment
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Intelipost\Shipping\Helper\Api $helperApi,
        \Intelipost\Shipping\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Intelipost\Shipping\Model\Shipment $shipment,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    )
    {
        $this->_helper    = $helper;
        $this->_helperApi = $helperApi;
        $this->_date      = $date;
        $this->_timezone  = $timezone;
        $this->_shipment  = $shipment;
    }

    /**
     * @param $collectionData
     * @throws \Exception
     */
    public function shippedRequestBody($collectionData)
    {
        $currentDateTimeUTC = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $localizedDateTimeISO = $this->_timezone->date(new \DateTime($currentDateTimeUTC))->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $this->event_date = (str_replace(' ', 'T', $localizedDateTimeISO)).'';

        $this->order_number = $collectionData['order_increment_id'];

        $requestBody = $this->prepareShippedRequestBody();
        $this->sendShippedRequest(json_encode($requestBody), $collectionData);

        return $this;
    }

    public function prepareShippedRequestBody()
    {
        $bodyObj               = new \stdClass();
        $bodyObj->order_number = $this->order_number;
        $bodyObj->event_date   = $this->event_date;
        array_push($this->shipArray, $bodyObj);
        return $this->shipArray;
    }

    /**
     * @param $requestBody
     * @param $collectionData
     * @throws \Exception
     */
    public function sendShippedRequest($requestBody, $collectionData)
    {
        $response = $this->_helperApi->apiRequest('POST', 'shipment_order/multi/shipped/with_date', $requestBody);
        $result = json_decode($response);

        if($result->status == 'ERROR') {
            $messages = null;
            $errorCount = 1;

            foreach ($result->messages as $_message) {
                $messages .= ' Erro ('. $errorCount . '): ' .$_message->text. "</br>";
                $errorCount++;
            }
            $this->message = $messages;

            $collectionFactory = $this->_shipment->load($collectionData['id']);
            $collectionFactory->setIntelipostStatus('error');
            $collectionFactory->setIntelipostMessage(str_replace('</br>', '', $this->message));
            $collectionFactory->save();
        }

        if($result->status == 'OK') {
            $collectionFactory = $this->_shipment->load($collectionData['id']);
            $collectionFactory->setIntelipostStatus(\Intelipost\Shipping\Model\Shipment::STATUS_SHIPPED);
            $collectionFactory->setIntelipostMessage('Ok.');
            $collectionFactory->save();
        }
    }

    public function getErrorMessages()
    {
        return $this->message;
    }
}
