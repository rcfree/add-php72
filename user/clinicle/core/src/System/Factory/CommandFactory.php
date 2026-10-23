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
class CommandFactory extends AbstractFactory {
	function initNamespaces()
    {
        $this->addNamespace("ClinicLE\\Manage");
    }

    function getClassType() {
		return "Command";
	}
	function getDefaultClassName() {
		return null;
	}
	function getLegacyClassType() {
		return "Manage";
	}

}
