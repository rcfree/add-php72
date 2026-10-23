<?php

namespace ClinicLE\Component\Event;
use ClinicLE\System\Event\ContentEvent;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 07/09/16
 * Time: 10:27
 */
class PageContentEvent extends ContentEvent
{
    private $page;
    function __construct($content, $page) {
        $this->page = $page;
        parent::__construct($content);
    }
    function html() {
        return $this->getContent();
    }
    function __call($name, $arguments)
    {
        if(!method_exists($this->page,$name)) {
            throw new \BadMethodCallException("Page class does not have '$name' method");
        }
    }
}