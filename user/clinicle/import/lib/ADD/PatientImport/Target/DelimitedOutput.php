<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_Target_DelimitedOutput {
	private $_outputDir;
	private $_atFailHeadings=true;
	private $_atSuccessHeadings=true;
	
	function prepare(ADD_PatientImport_Context $context) {
		//make output directory if it doesn't exist
		if (!is_dir($this -> _outputDir)) {
			mkdir($this -> _outputDir, 0755, true);
		}
		$outputLoc = $this -> _outputDir . "/patientlookup";

		//open file handles to write to
		//handles include patients, observations and failures
		$this -> _fhPats = fopen($outputLoc . "_pats.csv", "w");
		$this -> _fhObs = fopen($outputLoc . "_obs.csv", "w");
		$this -> _fhFail = fopen($outputLoc . "_fail.csv", "w");

		$dt = new DateTime();
		
		$this -> _successRows = 0;
		$this -> _partialRows = 0;
		$this -> _failRows = 0;
		$this -> _writeHeadings = true;
		$this -> _writeFailHeadings = true;
		$this -> _skipRows = 0;
		$this->_context = $context;
	}
	function beforePatientAction() {
		if (!$this->_atFailHeadings) return;
		$dt = new DateTime();
		
		$failHeadings = array_merge(array("D F L @ " . $dt -> format('Y-m-d H:i:s'), "Total @ " . $dt -> format('Y-m-d H:i:s'), "Issues @ " . $dt -> format('Y-m-d H:i:s'), "pmi_first_name", "pmi_last_name", "pmi_dob"), $this->_context->getSource()->getHeadings());
		fputcsv($this -> _fhFail, $failHeadings);
		$this->_atFailHeadings = false;
	}

	function outputFailPatient(ADD_PatientImport_MatchResult $matchResult) {
		$log -> error("!! Patient match FAIL");
		foreach ($issues as $issue) {

			$log -> info(" -" . $issue . "");
		}
		$p = $matchResult->getPmiPatient();
		$row = array($matchResult->getDScore()." " .$matchResult->getFScore()." ".$matchResult->getLScore(),$matchResult->getTotalScore(), $matchResult->getIssuesAsText(), $p['first_name'], $p['last_name'], $p['dob']);
		$row = array_merge($row, array_values($sourcePatient));
		fputcsv($this->_fhFail, $row);
		$this->_failRows++;
	}
	
	
	function outputSuccessPatient(ADD_PatientImport_MatchResult $matchResult) {
		$config = $this->_context;
		
		//write headings if first line
		if ($this->_atSuccessHeadings) {
			$obsHeadings = array("patient_id", "patient_hash");
			if ($config->getSetting('scoresInObs')) $obsHeadings = array_merge($obsHeadings, array("D F L @ " . $dt -> format('Y-m-d H:i:s'), "Total @ " . $dt -> format('Y-m-d H:i:s'), "Messages @ " . $dt -> format('Y-m-d H:i:s')));

			$obsHeadings = array_merge($obsHeadings,$df -> getHeadings());
			if ($config->getSetting('patientsDetailsInObs')) {
				$obsHeadings = array_merge($obsHeadings,array("patient_id", "orig_patient_id"));	
				$obsHeadings = array_merge($obsHeadings, ADD_PatientImport_Util::mapPmiPatientFields($p,"PMI_"));
			}
			$patHeadings = array_merge(array("D F L @ " . $dt -> format('Y-m-d H:i:s'), "Total @ " . $dt -> format('Y-m-d H:i:s'), "Messages @ " . $dt -> format('Y-m-d H:i:s'), "patient_id", "orig_patient_id"), $this->mapPmiPatientFields($p));

			fputcsv($this->_fhObs, $obsHeadings);
			fputcsv($this->_fhPats, $patHeadings);

			$this->_atSuccessHeadings = false;
		}

		$log -> info("** Patient match SUCCESS");
		foreach ($issues as $issue) {
			$log -> info(" -" . $issue . "");
		}
		$p = $matchResult->getPmiPatient();
		
		//create a patient hash so can integrate this data with data from other file
		$patSno = $p['main_pat_id'];
		$obsRow = array($patSno, md5($patSno . $p['first_name'] . $p['last_name'] . $p['dob']));



		//add issues as column in output
		$patRow = array($matchResult->getDScore()." " .$matchResult->getFScore()." ".$matchResult->getLScore()."(".$matchResult->getTotalScore().")", implode(";", $issues));

		//write observation line including the patient hash
		if ($config->getSetting('scoresInObs')) $obsRow = array_merge($obsRow,$patRow);
		$patRow = array_merge($patRow,array($patSno, $patientId));

		$obsRow = array_merge($obsRow, array_values($sourcePatient));

		if ($config->getSetting('patientsDetailsInObs') && $p) {
			$obsRow = array_merge($obsRow, array($patSno, $patientId));
			$obsRow = array_merge($obsRow, array_values($p));
		}
		fputcsv($this->_fhObs, $obsRow);

		if (!$p) $p=array();
		//merge issues info with patient details from PMI
		$patRow = array_merge($patRow, array_values($p));

		//write patient line including the patient hash
		fputcsv($this->_fhPats, $patRow);
		$totScore = $dscore + $fscore + $lscore;
		if ($totScore == 15) {
			$this->_successRows++;
		} else {
			$this->_partialRows++;
		}

	}
	
	function destroy() {
		fclose($this->_fhPats);
		fclose($this->_fhObs);
		fclose($this->_fhFail);
		
		$log->info("@@ PMI lookup complete. Processed {$rowCount} rows (".$this->_skipRows." skipped)");
		$log->info($this->_successRows." complete match patient lookups");
		$log->info($this->_partialRows." partial match patient lookups");
		$log->info($this->_failRows." failed patient lookups");
	}
}