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
class DbFieldFactory extends AbstractFieldFactory {
	
	public function preProcessArguments(&$arguments) {
		if(!isset($arguments[0])) {
			throw new \InvalidArgumentException("No field details provided to DbFieldFactory");
		}
		$arguments[0] = ADD_Util::pascalKeys($arguments[0]);
        $arguments[0]['Field']=$arguments[0]['Name'];
	}
	public function getClassFromArguments($arguments) {
		return $arguments['type'];
	}
}
