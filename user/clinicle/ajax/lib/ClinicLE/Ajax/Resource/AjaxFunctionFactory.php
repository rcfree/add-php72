<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Ajax\Resource;
use ClinicLE\Common\Factory\AbstractFactory;
use ClinicLE\Ajax\LegacyAjaxFunction;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class AjaxFunctionFactory extends AbstractFactory {
	function getClassType() {
		return "AjaxFunction";
	}
	function getDefaultClassName() {
		return null;
	}
    function getLegacyClassType() {
        return "Component";
    }
    function processInstance($class) {
        if(is_a($class,"ADD_Component_AjaxInterface")) {
            return new LegacyAjaxFunction($class);
        }
        return $class;
    }
}
