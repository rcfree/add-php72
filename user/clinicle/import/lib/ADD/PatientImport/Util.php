<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_Util {
	static function mapPmiPatientFields($pmiPatient,$prefix="") {
		$pmiMap = array('main_pat_id' => 'identifier', 'nhs_number' => 'nhs_no', 'title' => 'title', 'last_name' => 'last_name', 'first_name' => 'first_name', 'middle_name' => 'middle_name', 'dob' => 'date_of_birth', 'gender' => 'gender', 'ethnicity' => 'ethnic_group', 'marital_status' => 'marital_status', 'pat_addr1' => 'address_line_1', 'pat_addr2' => 'address_line_2', 'pat_addr3' => 'address_line_3', 'pat_addr4' => 'address_line_4', 'postcode' => 'postcode', 'homephone' => 'home_phone', 'workphone' => 'work_phone', 'death_indicator'=>'deceased', 'date_of_death' => 'date_of_death', 'gp_local_code' => 'local_code', 'gp_doh_code' => 'doh_code', 'gp_practicecode' => 'practice_code', 'gp_title' => 'gp_title', 'gp_forename' => 'gp_forename', 'gp_surname' => 'gp_surname', 'gp_addr1' => 'gp_address1', 'gp_addr2' => 'gp_address2', 'gp_addr3' => 'gp_address3', 'gp_addr4' => 'gp_address4', 'gp_postcode' => 'gp_postcode', 'gp_workphone' => 'gp_workphone');
		
		$fields = array();
		foreach ($pmiPatient as $pmiFieldName => $value) {
			$mapFieldName = ADD_Util::getArrayValue($pmiFieldName, $pmiMap);
			if(!$mapFieldName) continue;
			$fields[$prefix . $mapFieldName] = $value;
		}
		return $fields;
	}
		
}
