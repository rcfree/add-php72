<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_PmiMatcher {
	private $_pmi;
	private $_matchResultFactory;
	
	function __construct(LRB_PatientLookup $pmi, ADD_PatientImport_MatchResultFactory $matchResultFactory) {
		//set up PMI lookup
		$this -> _pmi = $pmi;
		$this->_matchResultFactory = $matchResultFactory;
	}
	
	function determineMatchResult($sourcePatient) {
		$log = ADD::log();
		$matchResult = $this->_matchResultFactory->createWithSource($sourcePatient);
		
		//if there is a patient returned by ID then check for a match
		if(!$this -> _getPatientById($matchResult)) {
			$matchResult->attemptFullMatch();
		}
		
		//if match fails then try to get patient by info
		if ($matchResult->isNoNameMatch()) {
			$log -> info("Patient lookup by identifier (" . $matchResult->getSourcePatientValue("identifier") . ") failed. Looking up by info (" . json_encode($matchResult->getSourcePatient()) . ")");
			$fail = $this->_getPatientByInfo($matchResult);
			if ($fail) {
				$matchResult->zeroScores();
			}
			else {
				$matchResult->attemptFullMatch();
			}
		}
		return $matchResult;
	}
	
	private function _getPatientById(ADD_PatientImport_MatchResult $matchResult) {
		$id = $matchResult->getSourcePatientValue("identifier");
		
		if (!$id) {
			$matchResult->addIssue("Patient identifier is null");
			return true;
		}
		$p = $this->_pmi -> get_patient_by_id($id);
		
		if (!$p) {
			$matchResult->addIssue("Patient identifier '$id' not found in DB");
			return true;
		}
		
		$matchResult->setPmiPatient($p);
		return false;
	}

	private function _getPatientByInfo(ADD_PatientImport_MatchResult $matchResult) {
		$info = $matchResult->getSourcePatient();
		
		$fail = false;
		if (strlen(ADD_Util::val("first_name",$info)) == 0) { $matchResult->addIssue("Patient first name missing");
			$fail = true;
		}
		if (strlen(ADD_Util::val("last_name",$info)) == 0) { $matchResult->addIssue("Patient last name missing");
			$fail = true;
		}
		if (strlen(ADD_Util::val("dob",$info)) == 0) { $matchResult->addIssue("Patient date of birth missing");
			$fail = true;
		}
		if ($fail) return true;
		
		$parentRecords=null;
		try {
			$pmiArgs = array("fname"=>ADD_Util::val('first_name',$info),"lname"=>ADD_Util::val('last_name',$info),"dob"=>ADD_Util::val('dob',$info));
			$patientRecords = $this->_pmi -> get_patient_records($pmiArgs);
			
			
		} catch(Exception $ex) {
			if (preg_match("/No patients were found meeting your criteria/", $ex -> getMessage())) {
				$matchResult->addIssue("Patient matching info " . json_encode($info) . " not found in DB (error message:".$ex->getMessage().")");
				return true;
			}
		}
		if ($patientRecords==null) {
			$matchResult->addIssue("Patient matching info " . json_encode($info) . " not found in DB");
			return true;
		}
		
		array_shift($patientRecords);
		
		$ps = array();
		foreach($patientRecords as $patientRecord) {
			$ps[]=$patientRecord[0];
		}
		
		if (count($ps) == 1) {
			$matchResult->setPmiPatient($ps[0]);
			return false;
		} elseif (count($ps) == 0) {
			$matchResult->addIssue("Patient matching info " . json_encode($info) . " not found in DB");
		} else {
			$matchResult->addIssue("Multiple patients found in DB matching info " . json_encode($info) . ". Patients:" . json_encode($ps));
		}
		return true;
	}

}
