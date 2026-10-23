<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_Context {
	private $_config;
	private $_source;
	private $_target;
	private $_plugins;
	
	function __construct(ADD_Config $config) {
		$pconfig = $config->getSection('PatientImport');
		$this->_config = $pconfig;
		
		$this->_source = ADD_Util::loadClass("ADD_PatientImport_Source_".$pconfig->assertSetting('source'));
		$this->_target = ADD_Util::loadClass("ADD_PatientImport_Target_".$pconfig->assertSetting('target'));
		
		//$this-> _plugins = new ADD_Tool_ClassSet($pconfig->getSetting('plugins'), "ADD_PatientLookup_Plugin");
	}
	function prepare(ADD_PatientImport $patientImport) {
		$this->_source->prepare($patientImport);
		$this->_target->prepare($patientImport);
	}
	function destroy(ADD_PatientImport $patientImport) {
		$patientImport->getSource()->destroy($patientImport);
		$patientImport->getTarget()->destroy($patientImport);
	}
	function getConfig() {
		return $this->_config;
	}
	function getSource() {
		return $this->_source;
	}
	function getTarget() {
		return $this->_target;
	}
	function getPlugins() {
		return $this->_plugins;
	}
}