<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 14/07/17
 * Time: 12:07
 */

namespace ClinicLE\Page\Event;


use ClinicLE\Page\AbstractPage;
use Symfony\Component\EventDispatcher\Event;

class AfterSubmitEvent extends Event
{
    private $submitEvent;
    private $submitPage;

    /**
     * @return SubmitEvent
     */
    public function getSubmitEvent()
    {
        return $this->submitEvent;
    }

    /**
     * @return AbstractPage
     */
    public function getSubmitPage()
    {
        return $this->submitPage;
    }

    function __construct(SubmitEvent $submitEvent, AbstractPage $submitPage) {
        $this->submitEvent = $submitEvent;
        $this->submitPage = $submitPage;
    }
}