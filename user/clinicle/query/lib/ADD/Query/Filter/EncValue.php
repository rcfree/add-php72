<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;

class ADD_Query_Filter_EncValue extends ADD_Query_Filter {
    function __construct($attrs) {
		parent::__construct($attrs);
        
        $field = $this->getAttr('field');
		$allowedFields = array(
			"id", 
			"parent_encounter_id",
			"badge",
			"is_complete",
			"is_locked",
			"is_deleted",
			"is_invalid",
			"is_autosave",
			"is_verified",
			"revision_no",
			"date_created",
			"date_updated"
		);
		if (!in_array($field, $allowedFields)) {
			throw new ADD_FieldDoesNotExistException("Encounter field '$field' does not exist or is not allowed");
		}
		
		$this->_encType = $this->getAttr("encounter","NONE");
		if($this->_encType) {
			$this->_encForm = ADD::db('encounter')->getEncounterFormWithType($this->_encType);
		}
	}
	
	function _filterPatientsEqual(ADD_Query_Result $result) {
		$field = $this->getAttr('field');
		$result->updateMatchingPatients("e.patient_id","encounter e","e.$field=? and e.encounter_form_id=?",array($this->_expValues, $this->_encForm['id']));
    }
	
    function _filterEncountersEqual(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		$field = $this->getAttr('field');
		$resultItem->updateMatchingEncounters($this->_encType,"e.id","encounter e","e.patient_id=? and e.$field".($this->_notFlag ? "!=" : "=")."? and e.encounter_form_id=?",array($resultItem->getPatientId(),$this->_expValues, $this->_encForm['id']));
    }
    
	function _filterEncountersBetween(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		$field = $this->getAttr('field');
		$resultItem->updateMatchingEncounters($this->_encType,"e.id","encounter e","e.patient_id=? and e.$field ".($this->_notFlag ? " not " : "")."between ? and ? and e.encounter_form_id=?",array($resultItem->getPatientId(),$this->_expValues[0],$this->_expValues[1], $this->_encForm['id']));
    }
	
}