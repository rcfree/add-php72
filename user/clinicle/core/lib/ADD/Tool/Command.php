<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_Command {
	
	static function executeWithException($command,$showOutput=false,$exceptionMessage="Problem executing command") {
		if(is_array($command)) $command = implode(" ",$command);
		$log = ADD::log();
				
		$returnVar = 0;
		$output = array();
		exec($command,$output,$returnVar);
		if($showOutput) {
			$log->info("Output:".implode("\n",$output));
		}
		if ($returnVar!=0) {
			throw new ADD_CommandReturnValueException($exceptionMessage,$command, $output,$returnVar);
		}
		return $output;
	}
	
	static function createSymLink($modName, $srcFile,$tgtFile,$description) {
		if (!file_exists($srcFile)) {
			return;
		}
		$tgtDir = dirname($tgtFile);
		if(!file_exists($tgtDir)) {
			mkdir($tgtDir,0777,true);
		}
        if(file_exists($tgtFile)) {
        	exec("rm -r $tgtFile");
		}
        exec("ln -s $srcFile $tgtFile");
	}
//	
	static function executeInBackground($command,$stdOutFile="/dev/null", $stdErrFile="/dev/null") {
        $output=null;
        exec($execCommand.' >'.$stdOutFile.' 2>'.$stdErrFile.'& echo $!',$output,$returnVar);
		return $output;
    }
}

class ADD_CommandReturnValueException extends Exception {
	private $_output=array();
	private $_returnVar;
	private $_command;
	function __construct($message,$command, $output=array(),$returnVar) {
		$this->_output=$output;
		$this->_command = $command;
		$this->_returnVar=$returnVar;
		parent::__construct($message);
	}
	function getCommand() {
		return $this->_command;
	}
	function getOutput() {
		return $this->_output;
	}
	function getReturnVar() {
		return $this->_returnVar;
	}
	function getOutputAsString() {
		return implode("\n",$this->_output);
	}
}
