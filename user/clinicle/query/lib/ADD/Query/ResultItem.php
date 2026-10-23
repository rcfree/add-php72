<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Query_ResultItem {
    private $_patientId;
	private $_encounterIds=array();
	private $_encounterData=array();
	private $_patientData=array();
	private $_excluded=false;
	
	public function __construct($patientId) {
		$this->_patientId=$patientId;
	}
	public function getPatientId() {
		return $this->_patientId;
	}
	public function setPatientId($patientId) {
		$this->_patientId = $patientId;
	}
	public function getEncounterIds($encounterType) {
		return array_key_exists($encounterType,$this->_encounterIds) ? $this->_encounterIds[$encounterType] : null;
	}
	public function setEncounterIds($encounterType,$encounterIds) {
		$this->_encounterIds[$encounterType] = $encounterIds;
	}
	public function isExcluded() {
		return $this->_excluded;
	}
	public function setExcluded($excluded) {
		$this->_excluded = $excluded;
	}
	public function updateMatchingEncounters($encounterType, $encounterIdField, $fromClause, $additionalWhere = null, $additionalParams = array()) {
		$encounterIds = ADD::db('query')->fetchDataRows($fromClause, $encounterIdField, $this->getEncounterIds($encounterType), $additionalWhere, $additionalParams);
		$this->setEncounterIds($encounterType,$encounterIds);
	}
	
	public function getPatientData($fieldName=null) {
		if(!$fieldName) return $this->_patientData;
		return ADD_Util::val($fieldName, $this->_patientData);
	}
	public function getEncounterRecords($encType) {
		$encData = $this->getEncounterData($encType);
		if(!$encData) return array();
		return array_values($encData);
	}
	public function getEncounterData($encType=null) {
		if(!$encType) return $this->_encounterData;
		return ADD_Util::val($encType, $this->_encounterData);
	}
	public function getFirstEncounterValue($encType,$field) {
		$encData = $this->getEncounterData($encType);
		if(!$encData) return null;	
		$encIds = array_keys($encData);
		$firstEncId = array_shift($encIds);
		$encItem = $encData[$firstEncId];
		return ADD_Util::val($field,$encItem);
	}
	
	public function getAllEncounterValues($encType,$field) {
		$encData = $this->getEncounterData($encType);
		if(!$encData) return array();	
		$encIds = array_keys($encData);
		$encValues = array();
		foreach($encIds as $encId) {
			$encItem = $encData[$encId];
			$encValues[]=ADD_Util::val($field,$encItem);
		}
		return $encValues;
	}
	public function hasEncounterIds($encType) {
		return array_key_exists($encType,$this->_encounterIds);
	}
	public function addEncounterData($type,$id,$data) {
		if(!isset($this->_encounterData[$type][$id])) {
			$this->_encounterData[$type][$id]=array();
		}
		$this->_encounterData[$type][$id]=array_merge($this->_encounterData[$type][$id],$data);
	}
	
	public function setPatientData($patientData) {
		$this->_patientData=$patientData;
	}
	
	public function addDataFromFieldData($fieldData) {
		foreach($fieldData as $encType => $obsData) {
			foreach($obsData as $idValue => $obsValues) {
				if(!isset($this->_encounterData[$encType][$idValue])) $this->_encounterData[$encType][$idValue]=array();
				$this->_encounterData[$encType][$idValue]=array_merge($this->_encounterData[$encType][$idValue],$obsValues);
			}
		}
	}
	
	public function addMetaDataFromRows($type,$idField,$rows) {
		foreach($rows as $row) {
			$idValue = $row[$idField];
			if(!isset($this->_encounterData[$type][$idValue])) {
				$this->_encounterData[$type][$idValue]=array();
			}
			$this->_encounterData[$type][$idValue]=$this->_encounterData[$type][$idValue] + $row;
		}
	}
	public function addPatientData($patient) {
		$this->_patientData = $this->_patientData + $patient;
	}
}
?>