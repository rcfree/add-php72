<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 23/05/17
 * Time: 09:46
 */

namespace ClinicLE\System\Event;


use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Module\ModuleCollection;
use ClinicLE\System\Service\Services;
use ClinicLE\System\System;
use Pimple\Container;
use Symfony\Component\EventDispatcher\Event;

class SystemEvent extends Event
{
    private $services;
    private $events;
    private $modules;
    private $systemConfig;

    function __construct(Container $services, EventDispatcher $events, ModuleCollection $modules, AbstractConfig
    $systemConfig) {
        $this->services = $services;
        $this->events = $events;
        $this->modules = $modules;
        $this->systemConfig = $systemConfig;
    }
    static function createFromSystem(System $system) {
        return new self($system->getServiceContainer(), $system->getEvents(),$system->getModules(), $system->getConfig());
    }
}