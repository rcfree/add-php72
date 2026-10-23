<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_EncData extends ADD_Query_Value {
	private $_noFields=false;
    function __construct($attrs) {
        parent::__construct($attrs);
        $this->_encType = $this->assertAttr("encounter");
        $this->_encForm = ADD::db('encounter')->getEncounterFormWithType($this->_encType);
		$this->_obsFields = ADD::db('encounter')->getObservationFieldsInEncounterForm($this->_encForm['id']);
		if(count($this->_obsFields)==0) {
			$this->_noFields=true;
		}
    }
    function addFieldsToResult(ADD_Query_Result $result) {
		$this->_fieldMap = array();
		
		foreach($this->_fields as $fieldName) {
			$this->_fieldMap[$fieldName]=ADD_Util::nameToLabel($fieldName);
		}
		
		$result->addEncounterFieldMap($this->_encType,$this->_fieldMap);
	}
    function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
    	if($this->_noFields) return;
    	$qdb = ADD::db('query');
		$encType = $this->_encType;
		$fields = $this->assertAttr("fields");
		
		$rows = $qdb->fetchEncounterData($fields,$this->_encForm,$resultItem);
		$resultItem->addMetaDataFromRows($encType,"id",$rows);
    }
	
}