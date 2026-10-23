<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
;

abstract class ADD_Query_Filter extends ADD_Query_Element {

	private $_opMethod;
	protected $_notFlag;
	protected $_expValues;
	protected $_filterType;
    
	const PATIENT = 1;
	const ENCOUNTER = 2;

	function __construct($attrs = array()) {
		parent::__construct($attrs);
		$this->_filterType = $this->getAttr("type",ADD_Query_Filter::PATIENT);
		$this->determineOp();
		$this->_expValues = $this->getExpectedValues();
	}

	function determineOp() {
		$op = $this->assertAttr('op', 'equal');
		$temp = explode("_", $op);
		$notFlag = false;
		if ($temp[0] == "not") {
			$notFlag = true;
			$op = implode("_", array_slice($temp, 1));
		}
		$opMethod = "_filter";
		switch($this->getFilterType()) {
			case ADD_Query_Filter::PATIENT:
				$opMethod.="Patients";
				break;
			case ADD_Query_Filter::ENCOUNTER:
				$opMethod.="Encounters";
				break;
		}
		
        $opMethod = $opMethod.ADD_Util::convertToPascal($op);
		if (!method_exists($this, $opMethod))
			throw new Exception("Operation '" . $opMethod . "' does not exist in filter");
		$this->_notFlag = $notFlag;
		$this->_opMethod = $opMethod;
	}

	function getValues() {
		return $this->getAttrs('values');
	}

	function getExpectedValues() {
		$values = $this->getAttr('value', $this->getAttr('values'));
		return $values;
	}
	
	function beforeFilter(ADD_Query_Result $result, ADD_Query_Run $run) {
	}

	function filterPatients(ADD_Query_Result $result) {
		if ($this->getFilterType() != ADD_Query_Filter::PATIENT)
			return;
		$opMethod = $this->_opMethod;
		$this->$opMethod($result);
	}

	function filterEncounters(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		if ($this->getFilterType() != ADD_Query_Filter::ENCOUNTER)
			return;
		$opMethod = $this->_opMethod;
		$this->$opMethod($resultItem,$result);
	}
	
	function filterAfterRetrieve(ADD_Query_ResultItem $resultItem) {
	}

	function getFilterType() {
		return $this->_filterType;
	}
	
	

}
