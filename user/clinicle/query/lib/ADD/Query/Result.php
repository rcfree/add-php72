<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
 
class ADD_Query_Result {
    private $_patientIds=null;
    private $_currentItemIndex=0;
	private $_patientFieldMap=array();
	private $_encounterFieldMap=array();
	
	function __construct(ADD_Query_Output $output, ADD_Query_Run $run) {
		$this->_output=$output;
		$this->_run = $run;
		
		foreach($run->getFilters() as $filter) {
			$filter->beforeFilter($this,$run);
        }
		foreach($run->getFilters() as $filter) {
			$filter->filterPatients($this);
        }
		
		$run->beforeData($this);
		
		$this->resetItems();
		$output->beforeOutput($this);
	}
	
	function getPatientFieldMap() {
		return $this->_patientFieldMap;
	}
	function getEncounterFieldMap() {
		return $this->_encounterFieldMap;
	}
    function setPatientIds($patientIds) {
    	$this->_patientIds=$patientIds;
    }
    function getPatientIds() {
        return $this->_patientIds;
    }
	
	function addPatientFieldMap($fieldMap) {
		$this->_patientFieldMap=$this->_patientFieldMap + $fieldMap;
	}
	function addEncounterFieldMap($type,$fieldMap) {
		if(!isset($this->_encounterFieldMap[$type])) $this->_encounterFieldMap[$type]=array();
		$this->_encounterFieldMap[$type]=$this->_encounterFieldMap[$type] + $fieldMap;
	}
	function updateMatchingPatients($patientIdField, $fromClause, $additionalWhere = null, $additionalParams = array()) {
		$orderBy = $this->_run->getPatientOrderBy();
		$patientIds = ADD::db('query')->fetchDataRows($fromClause, $patientIdField, $this->getPatientIds(), $additionalWhere, $additionalParams, $orderBy);
		$this->setPatientIds($patientIds);
	}
	function resetItems() {
		if($this->_patientIds===null) {
			$orderBy = $this->_run->getPatientOrderBy();
			$this->_patientIds = ADD::db('query')->fetchColumn("select id from patient".($orderBy ? " order by $orderBy":""),null,0);
		}
		$this->_currentItemIndex=0;
	}
	private function _nextItem() {
		if(!isset($this->_patientIds[$this->_currentItemIndex])) {
			return false;
		}
		$currentItem = new ADD_Query_ResultItem($this->_patientIds[$this->_currentItemIndex]);
		$this->_currentItemIndex++;
		return $currentItem;
	}
	
	function __destruct() {
		$this->_output->afterOutput($this);
	} 
	
	function nextResultItem() {
		$resultItem = $this->_nextItem();
		$run = $this->_run;
		if(!$resultItem) return false;
		
		foreach($run->getFilters() as $filter) {
			$filter->filterEncounters($resultItem,$this);
		}
		
		$run->retrieveData($resultItem);
		
		foreach($run->getFilters() as $filter) {
			$filter->filterAfterRetrieve($resultItem);
		}
		if($resultItem->isExcluded()) return $this->nextResultItem();
		return $resultItem;
	}
	function getOutput() {
		return $this->_output;
	}
	function getRun() {
		return $this->_run;
	}
}