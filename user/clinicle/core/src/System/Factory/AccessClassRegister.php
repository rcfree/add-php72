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
class AccessRegister extends Register {
	function __construct() {
		$this->addNamespace("ClinicLE");
	}
	function getClassType() {
		return "Access";
	}
	function getDefaultClassName() {
		return "ClinicLE\\Access\\Access";
	}
}
