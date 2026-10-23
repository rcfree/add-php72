<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;

class ADD_Query_Filter_EncType extends ADD_Query_Filter {
	private $_encForms = array();
    function __construct($attrs) {
		parent::__construct($attrs);
        
		foreach($this->_expValues as $encType) {
			$encForm = ADD::db('encounter')->getEncounterFormWithType($encType);
			$this->_encForms[] = $encForm;
		}
	}
	
	function _filterPatientsEqual(ADD_Query_Result $result) {
		$result->updateMatchingPatients("e.patient_id","encounter e","e.encounter_form_id=?",array($this->_encForm['id']));
    }
	function _filterPatientsIn(ADD_Query_Result $result) {
		$encFormIds = array_map(function($encForm) { return $encForm['id']; },$this->_encForms);
			
		$encFormIdPh = ADD_Query_Util::makePlaceholders($encFormIds);
		$result->updateMatchingPatients("e.patient_id","encounter e","e.encounter_form_id in ($encFormIdPh)",$encFormIds);
	}
    function _filterEncountersEqual(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {
		$field = $this->getAttr('field');
		
		foreach($this->_encForms as $encForm) {
			$params = array($resultItem->getPatientId(), $encForm['id']);
			$resultItem->updateMatchingEncounters($encForm['type'],"e.id","encounter e","e.patient_id=? and e.encounter_form_id ".($this->_notFlag ? "!=" : "=")."?",$params);
		}
    }
	
}