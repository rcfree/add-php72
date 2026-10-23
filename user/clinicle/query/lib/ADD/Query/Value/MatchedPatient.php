<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_MatchedPatient extends ADD_Query_Value {
	
	function addFieldsToResult(ADD_Query_Result $result) {
		parent::addFieldsToResult($result);
		$rows = $this->getAttr('rows');
		$row = $rows[0];
		$fieldMap = array();
		foreach($row as $name => $value) {
			$fieldMap["matched_$name"]="Case $name";
		}
		$result->addPatientFieldMap($fieldMap);
	}
	function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
		$mp = array();
		foreach($this->getAttr('rows') as $row) {
			if($row['Age']!=$resultItem->getPatientData('age')) continue;
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