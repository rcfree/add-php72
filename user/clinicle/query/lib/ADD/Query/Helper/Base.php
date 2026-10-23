<?php
class ADD_Query_Helper_Base {
	protected $_options=array();
	protected $_run;
	protected $_encounterLookup;
	
	function createRun($options=array()) {
		;
		;
		;
		;
		;
		
		$this->_encounterLookup = new ADD_Query_EncounterLookup(ADD::sys()->getModules());
		$this->_run = new ADD_Query_Run($this->_encounterLookup);
		$this->_options = new ADD_Config($options);
		
		return $this->_run;
	}
}