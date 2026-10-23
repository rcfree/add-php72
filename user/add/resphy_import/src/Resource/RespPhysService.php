<?php
namespace ADD\ResphyImport\Resource;

use ADD\ResphyImport\ExamFile;
use CLE;
use ClinicLE\Common\Util;

class RespPhysService {
	const RP_TYPE="resphy";
	const EXAM_TYPE="resphy-exam";
	const DETAIL_TYPE="resphy-exam-detail";
	const TEST_TYPE="resphy-exam-test";
	const AUDIT_TYPE="resp_exam_file";
	
	function addExamFileToPatient($patient, ExamFile $examFile, \ADD_PatientImport_MatchResult $matchResult) {
		$exam = $this->_createExamRecord($patient,$examFile);
		$this->_storeExamDetails($exam,$examFile,$matchResult);
		$this->_storeTestsInExamRecord($exam,$examFile);
		$this->_auditExamFile($examFile,$exam);
	}
	
	function _createExamRecord($patient,ExamFile $examFile) {
		$em = CLE::sys()->getEncounterManager();
		$respPhys = $em->domain(self::RP_TYPE,$patient['id']);
		if(!$respPhys->exists()) $respPhys->store();
		$matchingExam = $this->_getRecordMatchingExamFile($respPhys,$examFile);
		if(!$matchingExam) {
			$matchingExam = $em->newSection($respPhys->getPatient(),self::EXAM_TYPE);
			$matchingExam->store();
		}
		return $matchingExam;
	}
	
	function _getRecordMatchingExamFile($respPhys,ExamFile $examFile) {
		return null;
		$em = CLE::sys()->getEncounterManager();
		
		$examRecords = $em->sectionsByPatientAndType($respPhys->getPatient(),self::EXAM_TYPE);
		foreach($examRecords as $examRecord) {
			$examDetail = $em->child($examRecord,self::DETAIL_TYPE);
			$examDetail->retrieve();
			if($examDetail->getObsValue("exam_date")==$examFile->getExamDate() && $examDetail->getObsValue("exam_time")==$examFile->getExamTime()) return $examRecord;
		}
	}
		
	function _storeTestsInExamRecord(\ADD_EncounterRecord $examRecord,ExamFile $examFile) {
		$testNames = $examFile->getTestNames();
		foreach($testNames as $testName) {
			$testInfo = $examFile->getSingleTestInfo($testName);
			$this->_storeSingleTest($examRecord, $testInfo);
		}
	}
	
	function _storeSingleTest($examRecord, $testInfo, $test=null) {
		$em = CLE::sys()->getEncounterManager();
		//$log = ADD::log($this);
		$preValue = trim(Util::val("pre_value",$testInfo,""));
		$postValue = trim(Util::val("post_value",$testInfo,""));
		
		if(strlen($preValue)==0 && strlen($postValue)==0) {
			return null;
		}
		if($test == null) {
			$test = $em->newChild($examRecord,self::TEST_TYPE);
			//$log->info("create test:".$testInfo['test_type']);
		}
		else {
			//$log->info("update test:".$testInfo['test_type']);
		}
		$test->setObsValues($testInfo);
		$test->setEncData('is_complete',1);
		$test->setEncData('is_locked',1);
		$test->store();
	}
	
	function _storeExamDetails(\ADD_EncounterRecord $exam,ExamFile $examFile, \ADD_PatientImport_MatchResult
    $matchResult=null) {
		$em = CLE::sys()->getEncounterManager();
		
		$detail = $em->child($exam,self::DETAIL_TYPE);
		$values = array_merge($examFile->getPatientInfo(),$examFile->getExamInfo());
		
		$values['filename']=basename($examFile->getFileName());
		$values['pdf_filename']=$examFile->getTargetPdfFileName();
		if($matchResult) {
			$values['issues']=$matchResult->getIssuesAsText("\n");
			$values['score']=$matchResult->getTotalScore();
			$values['match_state']=$matchResult->getTotalScore()==15 ? "A" : "U";
		}
		$values['test_set_details']="Imported tests";
		$detail->setObsValues($values);
		$detail->setEncData('is_complete',1);
		$detail->setEncData('is_locked',1);
		$detail->store();
	}
	
	function updateExamFile($audit,$examFile) {
		$em = CLE::sys()->getEncounterManager();
		$exam = $em->sectionById($audit['record_id']);
		$exam->retrieve();
		
		$this->_storeExamDetails($exam,$examFile);
		
		$tests = $em->children($exam,self::TEST_TYPE);
		$testsByType = array();
		
		foreach($tests as $test) {
			$test->retrieve();
			$testsByType[$test->getObsValue("test_type")]=$test;
		}

		$testNames = $examFile->getTestNames();
		foreach($testNames as $testName) {
			$test = isset($testsByType[$testName]) ? $testsByType[$testName] : null;
			$testInfo = $examFile->getSingleTestInfo($testName);
			$this->_storeSingleTest($exam, $testInfo, $test);
		}
	}
	
	function _auditExamFile(ExamFile $examFile, $exam) {
		CLE::db('audit')->createAudit(array(
			"record_type"=>self::AUDIT_TYPE,
			"record_name"=>$examFile->generateUniqueId(),
			"action"=>"import",
			"record_id"=>$exam->getId()
		));
	}
	function isExamFileImported(ExamFile $examFile) {
		$imported = CLE::db('audit')->fetchFirst("select * from audit where record_type=? and record_name=? and action=?",array(self::AUDIT_TYPE,$examFile->generateUniqueId(),"import"));
		return $imported;
	}
	
}