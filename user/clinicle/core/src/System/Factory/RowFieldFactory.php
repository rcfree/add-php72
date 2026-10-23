<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Factory;
use ClinicLE\Common\Factory\AbstractFactory;
use ADD_Util;
/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class RowFieldFactory extends AbstractFieldFactory {
	
	public function preProcessArguments(&$arguments) {
		if(!isset($arguments[0])) {
			throw new \InvalidArgumentException("No field details provided to RowFieldFactory");
		}
		$arguments[0] = ADD_Util::pascalKeys($arguments[0]);
		$arguments[0]['Name'] = $arguments[0]['Field'];
	}
	public function getClassFromArguments($arguments) {
		return ADD_Util::convertToPascal(ADD_Util::val('Type',$arguments));
	}
	function getDefaultClassName() {
		return "ADD_Field_Markup";
	}
}
