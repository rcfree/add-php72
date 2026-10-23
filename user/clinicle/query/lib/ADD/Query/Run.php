<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Query_Run {
    private $_filters=array();
    private $_values=array();
    private $_encounterLookup;
	private $_patientOrderBy;
	
	function __construct(ADD_Query_EncounterLookup $encounterLookup, $patientOrderBy=null) {
		$this->_encounterLookup=$encounterLookup;
		$this->_patientOrderBy=$patientOrderBy;
	}

	function getEncounterLookup() {
		return $this->_encounterLookup;
	}
	
	function getPatientOrderBy() {
		return $this->_patientOrderBy;
	}
	
    function addFilter($filterType,$attrs=array()) {
        $filter = ADD_Util::loadClass("ADD_Query_Filter_".$filterType,$attrs,$this);
        $this->_filters[]=$filter;
    }
    
    function getFilters() {
        return $this->_filters;
    }
    function addValue($valueType,$attrs=array()) {
        $value = ADD_Util::loadClass("ADD_Query_Value_".$valueType,$attrs,$this);
        $this->_values[]=$value;
    }
   
	function beforeData(ADD_Query_Result $result) {
		foreach($this->_values as $value) {
			$value->addFieldsToResult($result);
        }
	}
	function retrieveData(ADD_Query_ResultItem $resultItem) {
		foreach($this->_values as $value) {
			$value->retrieveDataForPatient($resultItem,$this);
        }
	}
	function hasFilterWith($attrName,$attrValue) {
		foreach($this->_filters as $filter) {
			if ($filter->getAttr($attrName)==$attrValue) {
				return true;
			}
		}
		return false;
	}
	
	function getValues() {
		return $this->_values;
	}
}