<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_MatchedPatientRange extends ADD_Query_Value {
	
	function addFieldsToResult(ADD_Query_Result $result) {
		parent::addFieldsToResult($result);
		$rows = $this->getAttr('rows');
		$row = $rows[0];
		$fieldMap = array();
		foreach($row as $name => $value) {
			$fieldMap["matched_$name"]="Control $name";
		}
		$result->addPatientFieldMap($fieldMap);
	}
	function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
		$mp = array();
		$log = ADD::log($this);
		foreach($this->getAttr('rows') as $row) {
			$patientAge = $resultItem->getPatientData('age');
			$controlAge = $row['age'];
			
			$patientBmi = $resultItem->getFirstEncounterValue("dac-base-details","bmi");
			$controlBmi = $row['bmi'];
			
			$patientGender = $resultItem->getPatientData('gender');
			$controlGender = $row['gender'];
			
			if($patientAge < $controlAge - 5 || $patientAge > $controlAge+5) continue;
			
			if($patientBmi < $controlBmi -1 || $patientBmi > $controlBmi+1) continue;
				
			if($patientGender!=$controlGender) continue;
			
			$log->info("controlAge:$controlAge, patientAge:$patientAge, controlBmi:$controlBmi, patientBmi:$patientBmi, controlGender:$controlGender, patientGender:$patientGender");
		
			foreach($row as $field => $value) {
				$fieldName = "matched_$field";
				if(isset($mp[$fieldName])) {
					$mp[$fieldName].=";$value";
				}
				else {
					$mp[$fieldName]="$value";
				}
			}
		}
		$resultItem->addPatientData($mp);
	}
}