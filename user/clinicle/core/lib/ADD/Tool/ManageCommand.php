<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_ManageCommand {
	private $_manageCommand;
	private $_options;
	private $_stdOutFile="/dev/null";
	private $_stdErrFile="/dev/null";
	
	function __construct($manageCommand, $options) {
		$this->_manageCommand = $manageCommand;
		$this->_options = $options;
	}
	function setStdOutFile($stdOutFile) {
		$this->_stdOutFile = $stdOutFile;
	}
	function setStdErrFile($stdErrFile) {
		$this->_stdErrFile = $stdErrFile;
	}
	function setAppended($appended) {
		$this->_appended = $appended;
	}
	function isAppended() {
		return $this->_appended;
	}
	function executeInBackground() {
		$execCommand = $this->_createCommandString();
		$execString = $execCommand.' '.($this->_appended ? '>>' : '>').$this->_stdOutFile.' '.($this->_appended ? '2>>' : '2>').$this->_stdErrFile.' & echo $!';
		ADD::debug($execString);
		exec($execString,$output,$returnVar);
		return array($output,$returnVar);
	}
	function executeInForeground() {
	}
	function _createCommandString() {
		$command = $this->_manageCommand;
		$options=array();
		foreach($this->_options as $name => $option) {
			$options[]=$name.$option;
		}
		return "export ADD_PATH=".ADD::sys()->getPath().";export ADD_PROFILE=".ADD::sys()->getProfile()."; ".ADD::sys()->getPath()."/vendor/bin/clinicle $command ".implode(" ",$options);
	}
}
