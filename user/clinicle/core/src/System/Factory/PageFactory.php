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
class PageFactory extends AbstractFactory {
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
