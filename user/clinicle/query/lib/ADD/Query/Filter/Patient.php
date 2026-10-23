<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
date_default_timezone_set("GB");
class ADD_Query_Filter_Patient extends ADD_Query_Filter {
    private $_allowedFields = array("identifier","date_of_birth","first_name","last_name","deceased");
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->_field = $this->assertAttr("field");
        
        if(!in_array($this->_field,$this->_allowedFields)) {
            throw new ADD_FieldException("Patient '".$this->_field."' field not found or is not allowed");
        }
    }
    
    function _filterPatientsEqual(ADD_Query_Result $result) {
        $result->updateMatchingPatients("p.id","patient p","p.{$this->_field}".($this->_notFlag ? "!=" : "=")."?",array($this->_expValues));
    }
	
	function _filterPatientsLike(ADD_Query_Result $result) {
        $result->updateMatchingPatients("p.id","patient p","p.{$this->_field} ".($this->_notFlag ? "not like" : "like")." ?",array($this->_expValues));
    }
	
    function getFilterType() {
		return ADD_Query_Filter::PATIENT;
	}
	function filterEncounters(ADD_Query_ResultItem $resultItem, ADD_Query_Result $result) {}
}