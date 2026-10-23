<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_Age extends ADD_Query_Value {
    function addFieldsToResult(ADD_Query_Result $result) {
		$fieldMap = array("age"=>"Age");
        $result->addPatientFieldMap($fieldMap);
    }
	
    function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
    	$now = date("Y-m-d");
    	$patient = ADD::db('query')->fetchPatientData(array("TIMESTAMPDIFF(YEAR, p.date_of_birth, '$now') as age"),$resultItem);
        $resultItem->addPatientData($patient);
    }
}