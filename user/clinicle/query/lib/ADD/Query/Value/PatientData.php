<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_PatientData extends ADD_Query_Value {
    function addFieldsToResult(ADD_Query_Result $result) {
		$fieldMap = array();
		foreach($this->_fields as $fieldName) {
			$fieldMap[$fieldName]=ADD_Util::nameToLabel($fieldName);
		}
        $result->addPatientFieldMap($fieldMap);
    }
	
    function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
    	$patient = ADD::db('query')->fetchPatientData($this->getAttr("fields"),$resultItem);
        $resultItem->addPatientData($patient);
    }
}