<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_PatientImport_MatchResult {
	private $_dScore=0;
	private $_fScore=0;
	private $_lScore=0;
	private $_issues = array();
	private $_pmiPatient;
	private $_sourcePatient;
	private $_matchDone=false;
	private $_matchNameSegment=true;
	
	function __construct($sourcePatient) {
		$this->_sourcePatient = $sourcePatient;
	}
	function shouldMatchNameSegment($truncateNames) {
		$this->_matchNameSegment=$matchNameSegment;
	}
	function getDScore() {
		return $this->_dScore;
	}
	function getFScore() {
		return $this->_fScore;
	}
	function getLScore() {
		return $this->_lScore;
	}
	function getTotalScore() {
		$total = $this->_dScore + $this->_fScore + $this->_lScore;
		return $total ? $total :'0';
	}
	function attemptFullMatch() {
		$dateScore = $this->_dateMatch($this->_pmiPatient['dob'], $this->_sourcePatient['dob']);
		$pmiFirstName = strtoupper($this->_pmiPatient['first_name']);
		$pmiLastName = strtoupper($this->_pmiPatient['last_name']);
		
		$srcFirstName = strtoupper($this->_sourcePatient['first_name']);
		$srcLastName = strtoupper($this->_sourcePatient['last_name']);
		
		$fnameScore = $this->_nameMatch($pmiFirstName, $srcFirstName,$srcLastName,"first");
		$lnameScore = $this->_nameMatch($pmiLastName,$srcLastName,$srcFirstName,"last");
		$this->_dScore = $dateScore;
		$this->_fScore = $fnameScore;
		$this->_lScore = $lnameScore;
		$this->_matchDone=true;
	}
	
	function getPmiPatient() {
		return $this->_pmiPatient;
	}
	function setPmiPatient($pmiPatient) {
		$this->_pmiPatient=$pmiPatient;
	}
	function getSourcePatient() {
		return $this->_sourcePatient;
	}
	function getSourcePatientValue($field) {
		return ADD_Util::val($field,$this->_sourcePatient);
	}
	function setSourcePatient($sourcePatient) {
		$this->_sourcePatient=$sourcePatient;
	}
	function zeroScores() {
		$this->setScores(0,0,0);
	}
	function setScores($fscore,$lscore,$dscore) {
		$this->_fScore = $fscore;
		$this->_lScore = $lscore;
		$this->_dScore = $dscore;
	}
	
	function isNoNameMatch() {
		return $this->_fScore == 0 && $this->_lScore == 0;
	}
	function _dateMatch($pmiDate, $srcDate) {
		if ($pmiDate == $srcDate) {
			$this->_issues[] = "DOB match (pmi:$pmiDate, src:$srcDate)";
			return 5;
		}
		if (!$srcDate || strlen($srcDate) == 0) {
			$this->_issues[] = "Source DOB empty (pmi:$pmiDate)";
			return 2;
		}
		global $opts;
		$returnVal = 0;
		if (isset($opts['d']))
			$returnVal = 4;
		$this->_issues[] = "DOB not equal (pmi:$pmiDate, src:$srcDate)";
		return $returnVal;
	}
	
	function _checkMatch($pmiName, $srcName, $msg = "") {
		$pmiNameLen = strlen($pmiName);
		$srcNameLen = strlen($srcName);
		if ($pmiNameLen == 0)
			return 0;
		$msg = ($msg ? ucfirst($msg." ") : "");
		
		if ($srcNameLen == 1) {
			if (substr($pmiName, 0, 1) == $srcName) {
				$this->_issues[] = $msg."name initial matches (pmi:$pmiName, src:$srcName)";
				return 3;
			} else {
				return 0;
			}
		}
		
		
		//SUCCESS: if file and DB names match return true
		if ($pmiName == $srcName) {
			$this->_issues[] = $msg."name match (pmi:$pmiName, src:$srcName)";
			return 5;
		}

		//if name is shorter than db name:
		//SUCCESS: if that portion of db name is equal to the name
		
		$portIssue = null;
		$portScore = 0;
		if ($pmiNameLen < $srcNameLen) {
			if (substr($srcName, 0, $pmiNameLen) == $pmiName) {
				$portIssue = $msg."name portion matches (pmi:$pmiName, src:$srcName)";
				$portScore = $this->_matchNameSegment ? 5 : 3;
			}
		}
		
		$fuzzIssue = null;
		$fuzzScore = 0;
		//SUCCESS if levenstein score <=3 between dbName and fileName1
		for($lscore=1;$lscore<=2;$lscore++) {
			if (levenshtein($pmiName, $srcName) <= $lscore) {
				$fuzzIssue = $msg."name fuzzy match [l-score <=$lscore] (pmi:$pmiName, src:$srcName)";
				$fuzzScore = 5-$lscore;
			}
		}
		
		if($portScore==0 && $fuzzScore==0) return 0;
		
		if($portScore >= $fuzzScore) {
			$this->_issues[]=$portIssue;
			return $portScore;
		}
		
		$this->_issues[]=$fuzzIssue;
		return $fuzzScore;
	}

	function _nameMatch($pmiName, $srcName1, $srcName2,$type) {
		//SUCCESS: if first and last names match
		$flmatch = $this->_checkMatch($pmiName,$srcName1,$type);
		if ($flmatch)
			return $flmatch;

		//SUCCESS: if first and last names are swapped over
		$lfmatch = $this->_checkMatch($pmiName, $srcName2, "Swapped $type");
		if ($lfmatch) {
			return $lfmatch-1;
		}
		$this->_issues[] = ucfirst($type)." name does not match (pmi:$pmiName, src:$srcName1 OR $srcName2";
		return 0;
	}
	
	function getIssuesAsText($joinChar=";") {
		return implode($joinChar, $this->_issues);
	}
	function addIssue($issue) {
		$this->_issues[]=$issue;
	}
	
	function __toString() {
		return "issues:".$this->getIssuesAsText().", score:".$this->getTotalScore();
	}
}