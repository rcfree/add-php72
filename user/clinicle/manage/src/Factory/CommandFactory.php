<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Manage\Factory;
use ClinicLE\Common\Factory\AbstractFactory;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class CommandFactory extends AbstractFactory {
	
	function getClassType() {
		return "Command";
	}
	function getDefaultClassName() {
		return null;
	}
	function getLegacyClassType() {
		return "Manage";
	}
	function processInstance($class) {
	    return $class;
		if(!is_a($class,"ADD_Component")) {
			return $class;
		}
		$legacy = new \Clinicle\Component\LegacyComponent($class);
		return $legacy;
	}
}
