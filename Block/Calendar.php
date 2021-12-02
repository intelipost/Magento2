<?php
/*
 * @package     Intelipost_Shipping
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
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
