<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Module;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;

/**
 * Description of AbstractResource
 *
 * @author rob
 */
abstract class AbstractResource {
	abstract function initResource(Container $services, EventDispatcher $events);
}
