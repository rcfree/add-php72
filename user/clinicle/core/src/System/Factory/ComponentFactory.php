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
class ComponentFactory extends AbstractFactory {
	function initNamespaces() {
		//$this->addClassItem("Form_File","ClinicLE\\Form\\Component\\FileFormComponent");
		//$this->addNamespace("Clinicle\\Patient");
		//$this->addNamespace("Clinicle\\Home");
		$this->addNamespace("TBIT2\Casemanage");
		$this->addClassItem("IndexCase","TBIT2\\Casemanage\\Component\\IndexCaseComponent");
        
		//$this->addNamespace("Clinicle");
		//$this->addClassItem("Encounter_Domain_ManualEnrol","Clinicle\\Domain\\Component\\DomainMenuComponent");
		//$this->addClassItem("Encounter_Section","Clinicle\\Section\\Component\SectionMenuComponent");
		//$this->addClassItem("LungFunction","ADD_Component_LungFunction");
	}
	function getClassType() {
		return "Component";
	}
	function getDefaultClassName() {
		return null;
	}
	function getLegacyClassType() {
		return "Component";
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
