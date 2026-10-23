<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
date_default_timezone_set("GB");
class ADD_Query_Filter_Age extends ADD_Query_Filter {
    private $_today;
	protected $_filterType=ADD_Query_Filter::PATIENT;
	
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->_today = $this->assertAttr('today');
        
    }
    
    function getObservedValue($patient,$record=null) {
        if(!$patient) throw new ADD_PatientException("Patient not provided to Age filter");
        $age = ADD_Field_Util::age(ADD_Util::val('date_of_birth',$patient),$this->_today);
        return $age;
    }
     
    function _filterPatientsEqual(ADD_Query_Result $result) {
    	$ageFunction = "TIMESTAMPDIFF(YEAR, p.date_of_birth, ?)";
        $result->updateMatchingPatients("p.id","patient p","$ageFunction=?",array($this->_today,$this->_expValues));
    }
	
	function _filterPatientsBetween(ADD_Query_Result $result) {
    	$ageFunction = "TIMESTAMPDIFF(YEAR, p.date_of_birth, ?)";
		$params = array($this->_today);
		
		$params = array_merge($params,$this->_expValues);
		
        $result->updateMatchingPatients("p.id","patient p","$ageFunction between ? and ?",$params);
    }
    
    function _filterPatientsIn(ADD_Query_Result $result) {
        $ageFunction = "TIMESTAMPDIFF(YEAR, p.date_of_birth, ?)";
        $params = ADD_Query_Util::makePlaceholders($this->_expValues);
        //$ageFunction = "DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(p.date_of_birth, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(p.date_of_birth, '00-%m-%d'))";
        
        $result->updateMatchingPatients("p.id","patient p","$ageFunction in (".$params.")",array_merge(array($this->_today),$this->_expValues));
	}
}