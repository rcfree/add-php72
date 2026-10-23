<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Filter_Identifiers extends ADD_Query_Filter {
    protected $_filterType=ADD_Query_Filter::PATIENT;
	
    function _filterPatientsEqual(ADD_Query_Result $result) {
        $result->updateMatchingPatients("p.id","patient p","p.identifier ".($this->_notFlag ? "!=" : "=")." ?",array($this->_expValues));
    }
	function _filterPatientsIn(ADD_Query_Result $result) {
		$inClause = ADD_Query_Util::makePlaceholders($this->_expValues);
		$result->updateMatchingPatients("p.id","patient p","p.identifier ".($this->_notFlag ? "not in" : "in")." ($inClause)",$this->_expValues);
    }
}