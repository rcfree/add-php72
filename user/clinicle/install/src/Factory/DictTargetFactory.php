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
class DictTargetFactory extends AbstractFactory {
        function initNamespaces() {
            $this->addNamespace("ClinicLE\Install\Dictionary");
        }
        
	function getClassType() {
		return "DictTarget";
	}
	function getDefaultClassName() {
		return "ADD_ModuleInstall_Dictionary_DictionaryTableTarget";
	}
        function getLegacyClassType() {
            return "ModuleInstall_Dictionary_Target";
        }
        public function searchLegacy($classLabel) {
            $classLabel.="Target";
            return parent::searchLegacy($classLabel);
	}
	
}
