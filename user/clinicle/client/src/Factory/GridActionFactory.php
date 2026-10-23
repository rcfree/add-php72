<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Client\Factory;
use ClinicLE\Common\Factory\AbstractFactory;

/**
 * Description of CommandClassRegister
 *
 * @author rcf8
 */
class GridActionFactory extends AbstractFactory {
	function getClassType() {
		return "GridAction";
	}
	function getDefaultClassName() {
		return "ClinicLE\\Client\\LegacyGridAction";
	}
	function getLegacyClassType() {
	    return "Client_Action";
    }
	
}
