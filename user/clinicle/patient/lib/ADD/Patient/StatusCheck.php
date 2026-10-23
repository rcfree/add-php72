<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Patient_StatusCheck {
	function changedPatient($pagData,$pmiData) {
		$changedFields = $this->getAllChangedFields($pagData,$pmiData);
		if(count($changedFields)==0) return false;
		$fullData = array();
		foreach($pagData as $itemData) {
			if(!is_array($itemData)) continue;
			$fullData = $fullData + $itemData;
		}
		foreach($changedFields as $changedFieldName => $changedFieldValue) {
			$fullData[$changedFieldName]=$changedFieldValue;
		}
		unset($fullData['id']);
		if(isset($fullData['address_id'])) unset($fullData['address_id']);
		if(isset($fullData['gp_id'])) unset($fullData['gp_id']);
		
		return array($fullData,$changedFields);
	}
	function getAllChangedFields($pagData,$pmiData) {
		$changedFields = array();
		$patientFieldMap = array(
			"main_pat_id"=> "identifier",
			"nhs_number"=> "nhs_no",
			"title"=> "title",
			"last_name"=>"last_name",
			"first_name"=>"first_name",
			"dob"=>"date_of_birth",
			"gender"=>"gender",
			"ethnicity"=>"ethnic_group",
			"marital_status"=>"marital_status",
			"date_of_death"=> "date_of_death",
			"death_indicator"=>"deceased"
		);
		$changedFields+=$this->_updatedData(0,$pagData,$pmiData,$patientFieldMap);
		
		$gpFieldMap = array(
			'gp_local_code'=>'local_code',
			'gp_doh_code'=>'doh_code',
			'gp_practicecode'=>'practice_code',
			'gp_title'=>'gp_title',
			'gp_forename'=>'gp_forename',
			'gp_surname'=>'gp_surname',
			'gp_addr1'=>'gp_address1',
			'gp_addr2'=>'gp_address2',
			'gp_addr3'=>'gp_address3',
			'gp_addr4'=>'gp_address4',
			'gp_postcode'=>'gp_postcode',
			'gp_workphone'=>'gp_workphone'
		);
		$changedFields+=$this->_updatedData(2,$pagData,$pmiData,$gpFieldMap);
	
		$addressFieldMap = array(
			'pat_addr1'=>'address_line_1',
			'pat_addr2'=>'address_line_2',
			'pat_addr3'=>'address_line_3',
			'pat_addr4'=>'address_line_4',
			'postcode'=>'postcode',
			'homephone'=>'home_phone',
			'workphone'=>'work_phone',
		);
		$changedFields+=$this->_updatedData(1,$pagData,$pmiData,$addressFieldMap);
		return $changedFields;
	}
	
	function _updatedData($index,$pagData,$pmiData,$fieldMap) {
		if(!isset($pagData[$index])) return array();
		$existingData = $pagData[$index];
		$mappedPmiData = $this->_remapArray($fieldMap,$pmiData);
		$changedFields = $this->_extractChangedFields($existingData,$mappedPmiData,array_values($fieldMap));
		return $changedFields;
	}
	
	function _remapArray($fieldMap,$pmiData) {
		$remap = array();
		foreach($pmiData as $pmiName => $pmiValue) {
			if(!isset($fieldMap[$pmiName])) continue;
			$mapFieldName = $fieldMap[$pmiName];
			$remap[$mapFieldName]=$pmiValue;
		}
		return $remap;
	}
	function _extractChangedFields($origData,$newData,$fieldsToCheck) {
		$changedFields = array();
		
		foreach($fieldsToCheck as $fieldToCheck) {
			
			if(ADD_Util::val($fieldToCheck,$origData)==ADD_Util::val($fieldToCheck,$newData)) continue;
			$newValue = $newData[$fieldToCheck];
			$changedFields[$fieldToCheck]=ADD_Util::isEmpty($newValue) ? null : $newData[$fieldToCheck];	
		}
		return $changedFields;
	}
}
class ADD_PatientFieldMissingException extends Exception {}