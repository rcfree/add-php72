<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install\Factory;
use ClinicLE\Common\Factory\AbstractFactory;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class DictPluginFactory extends AbstractFactory {
	function getClassType() {
		return "DictPlugin";
	}
	function getDefaultClassName() {
		return "ADD_Dictionary_Plugin";
	}
        function getLegacyClassType() {
            return "ModuleInstall_Dictionary_Plugin";
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
