<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install\Event;
use Symfony\Component\EventDispatcher\Event;
use ClinicLE\Module\Module;

class ModuleInstallEvent extends Event {
    private $module;

	function __construct(Module $module) {
	    $this->module = $module;
	}
	function getModule() {
		return $this->module;
	}

	function setModule($module) {
		$this->module = $module;
	}
}