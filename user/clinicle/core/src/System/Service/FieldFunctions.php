<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Service;

/**
 * Description of FieldFunctions
 *
 * @author rob
 */
class FieldFunctions {
	private $functions = array();
	function callFunction($name,$arguments) {
		$callback = $this->functions[$name];
		$output = call_user_func_array($callback, $arguments);
		return $output;
	}
	function addFunction($name,$function) {
		$this->functions[$name]=$function;
	}
	function hasFunction($name) {
		return isset($this->functions[$name]);
	}
	
	public function __call($name,$arguments=array()) {
		return $this->callFunction($name,$arguments);
	}
}
