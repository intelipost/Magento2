<?php
/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Intelipost\Shipping\Block;

class Calendar extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
    )
    {
        $this->setTemplate('calendar.phtml');
        parent::__construct($context);
    }

    public function getAjaxCalendarUrl()
    {
        return $this->getUrl('intelipost/calendar/index');
    }

    public function getAjaxScheduleUrl()
    {
        return $this->getUrl('intelipost/schedule/index');
    }

    public function getAjaxScheduleStatusUrl()
    {
        return $this->getUrl('intelipost/schedule/status');
    }
}
