<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Exception;

class ExecuteFailedException extends \Exception {
	private $command;
	private $output;
	private $returnVar;
	
	function __construct($exceptionMessage,$command, $output,$returnVar) {
		$this->command = $command;
		$this->output = $output;
		$this->returnVar = $returnVar;
		return parent::__construct($exceptionMessage);
	}
	function getCommand() {
		return $this->command;
	}

	function getOutput() {
		return $this->output;
	}

	function getReturnVar() {
		return $this->returnVar;
	}


}