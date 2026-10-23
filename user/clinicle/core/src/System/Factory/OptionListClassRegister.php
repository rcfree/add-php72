<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\ClassRegister;
use ClinicLE\ClassRegistry\Register;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class OptionListClassRegister extends Register {
	function __construct() {
		$this->addNamespace("ClinicLE\\Entity");
	}
	function getClassType() {
		return "OptionList";
	}
	function getDefaultClassName() {
		return null;
	}
	
}
