<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Data_PatientService {
	function retrievePatientFromMatchResult(ADD_PatientImport_MatchResult $matchResult) {
		//if patient doesn't exist in ADD 
		$pdb = ADD::db('patient');
		$pmiPatient = $matchResult->getPmiPatient();
		$patient = $pdb->getPatientWithIdentifier($pmiPatient['main_pat_id']);
				
		//then add them
		if(!$patient) {
			$patientData = ADD_PatientImport_Util::mapPmiPatientFields($pmiPatient);
			list($patient,$address,$gp) = $pdb->createPatientDemographics($patientData);
		}
		return $patient;
	}
	
	function retrievePatientWithSpecialIdentifier($specialIdentifier) {
		$patient = ADD::db('patient')->getPatientWithIdentifier($specialIdentifier);
			
		if(!$patient) {
			$patient = ADD::db()->insert("patient",array("identifier"=>$specialIdentifier,"special_status"=>"held","first_name"=>$specialIdentifier,"last_name"=>$specialIdentifier,"date_of_birth"=>"0000-00-00","is_deleted"=>1));
		}
		return $patient;
	}
}