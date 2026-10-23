<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Factory;
use ClinicLE\Common\Factory\AbstractFactory;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class AbstractFieldFactory extends AbstractFactory {
	
	function getClassType() {
		return "Field";
	}
    function getLegacyClassType() {
        return array("Field","Field_Container","Field_Text","Field_List", "Field_Button", "Field_Submit");
    }
	function getDefaultClassName() {
		return null;
	}
	function processInstance($class) {
	    return $class;
		if(!is_a($class,"ADD_Field")) {
			return $class;
		}
		$legacy = new \Clinicle\Entity\Field\LegacyField($class);
		return $legacy;
	}
	
	public function newInstance($classLabel, $arguments = array()){
		if(is_array($classLabel)) {
			$arguments = array($classLabel);
			$classLabel = $this->getClassFromArguments($classLabel);
		}
		return parent::newInstance($classLabel,$arguments);
	}
	
	public function getClassFromArguments($arguments) {
		throw new \BadMethodCallException("Unable to create new instance from arguments");
	}
	
}
