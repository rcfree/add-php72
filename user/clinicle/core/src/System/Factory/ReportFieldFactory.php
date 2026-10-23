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
class ReportFieldFactory extends AbstractFieldFactory {
	
	public function getClassFromArguments($arguments) {
		return ADD_Util::convertToPascal(ADD_Util::val('Type',$arguments,'text'));
	}
	function getClassType() {
		return "ReportField";
	}
    function getLegacyClassType() {
        return array("ReportField");
    }
}
