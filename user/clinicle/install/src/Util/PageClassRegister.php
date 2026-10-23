<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\ClassRegister;
use ClinicLE\Common\ClassRegister;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class PageClassRegister extends ClassRegister {
	function __construct() {
		$this->addNamespace("Clinicle\Home");
		$this->addNamespace("Clinicle\Menu");
		$this->addNamespace("Clinicle\Client");
		$this->addNamespace("Clinicle");
		$this->addNamespace("Clinicle\Form");
		$this->addNamespace("Clinicle\Patient");
                $this->addNamespace("TBIT2\Casemanage");
	}
	function getClassType() {
		return "Page";
	}
	function getDefaultClassName() {
		return "ADD_Page";
	}
        function getLegacyClassType() {
            return "Page";
        }
        /*
	function searchLegacy($classLabel) {
            
		if($classLabel == null) {
			return "Clinicle\\Page\\LegacyPage";
		}
		return parent::searchLegacy($classLabel);
	}
	*/
}
