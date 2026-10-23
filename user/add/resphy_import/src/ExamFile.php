<?php

namespace ADD\ResphyImport;
use ADD_Field_Util;
use ClinicLE\Common\Util;

class ExamFile {
	private $_lastName;
	private $_tests = array();
	private $_firstName;
	private $_identifier;
	private $_dateOfBirth;
	private $_dateTimeOfTests;
	private $_predictedSource;
	private $_consultant;
	private $_sourcePdfFileName = null;
	private $_targetPdfFileName = null;
	private $_comments = null;
	private $_uniqueId;
	private $_file;
	
	function __construct($file) {
		$this->_file = $file;
	}
	function getPatientInfo() {
		return array(
			"identifier"=>$this->getIdentifier(),
			"first_name"=>$this->getFirstName(),
			"last_name"=>$this->getLastName(),
			"dob"=>$this->getDateOfBirth()
		);
	}
	
	function getExamInfo() {
		return array(
			"exam_date"=>$this->getExamDate(),
			"exam_time"=>$this->getExamTime(),
			"height"=>$this->_height,
			"weight"=>$this->_weight,
			"predicted_source"=>$this->_predictedSource,
			"consultant"=>$this->_consultant,
			"source"=>"1",
			"date_imported"=>ADD_Field_Util::now()
		);
	}
	function getSingleTestInfo($testName) {
		$test = $this->getTest($testName);
		$info = array(
			"test_date"=>$this->getExamDate(),
			"test_type"=>Util::val("TEST",$test),
			"units"=>Util::val("UNITS",$test),
			"predicted"=>Util::val("PREDICTEDS",$test),
			"pre_value"=>Util::val("PRE-TEST TEST SEL. MEASURE",$test),
			"post_value"=>Util::val("POST SEL. TEST SEL. MEASURE",$test)
		);
		if(!Util::isEmpty($info['predicted'])) {
			if(!Util::isEmpty($info['pre_value'])) {
				$info["pre_pc_predicted"]=number_format(($info['pre_value']/$info['predicted'])*100,2);
			}
			if(!Util::isEmpty($info['post_value'])) {
				$info["post_pc_predicted"]=number_format(($info['post_value']/$info['predicted'])*100,2);
			}
		}
		return $info;
	}
	function getFileName() {
		return $this->_file->getFilename();
	}
	function getFile() {
		return $this->_file;
	}
	function getFirstName() {
		return $this->_firstName;
	}
	function getLastName() {
		return $this->_lastName;
	}
	function getIdentifier() {
		return $this->_identifier;
	}
	function getDateOfBirth() {
		return $this->_dateOfBirth;
	}
	function getHeight() {
		return $this->_height;
	}
	function getWeight() {
		return $this->_weight;
	}
	function getTests() {
		return $this->_tests;
	}
	function setFirstName($firstName) {
		$this->_firstName=$firstName;
	}
	function setLastName($lastName) {
		$this->_lastName=$lastName;
	}
	function setIdentifier($identifier) {
		$this->_identifier=$identifier;
	}
	function setDateOfBirth($dateOfBirth) {
		$this->_dateOfBirth=$dateOfBirth;
	}
	function setDateTimeOfTests($dateTimeOfTests) {
		$this->_dateTimeOfTests=$dateTimeOfTests;
	}
	function setHeight($height) {
		$this->_height=$height;
	}
	function setWeight($weight) {
		$this->_weight=$weight;
	}
	function setTests($tests) {
		$this->_tests = $tests;
	}
	function getTest($testName) {
		return Util::val($testName,$this->_tests);
	}
	function getTestNames() {
		return array_keys($this->_tests);
	}
	function getDateTimeOfTests() {
		return $this->_dateTimeOfTests;
	}
	function getExamDate() {
		$temp = explode(" ",$this->_dateTimeOfTests);
		return $temp[0];
	}
	function getExamTime() {
		$temp = explode(" ",$this->_dateTimeOfTests);
		return $temp[1];
	}
	function getPredictedSource() {
		return $this->_predictedSource;
	}
	
	function setPredictedSource($predictedSource) {
		$this->_predictedSource = $predictedSource;
	}
	
	function getConsultant() {
		return $this->_consultant;
	}
	function setConsultant($consultant) {
		$this->_consultant = $consultant;
	}
	function setSourcePdfFileName($pdfFileName) {
		$this->_sourcePdfFileName = $pdfFileName;
	}
	function getSourcePdfFileName() {
		return $this->_sourcePdfFileName;
	}
	function setTargetPdfFileName($pdfFileName) {
		$this->_targetPdfFileName = $pdfFileName;
	}
	function getTargetPdfFileName() {
		return $this->_targetPdfFileName;
	}
	function getComments() {
		return $this->_comments;
	}
	function setComments($comments) {
		$this->_comments = $comments;
	}
	function generateUniqueId() {
		if($this->_uniqueId) {
			return $this->_uniqueId;
		}
		$parts = array($this->getIdentifier(),$this->getFirstName(),$this->getLastName(),$this->getDateTimeOfTests());
		$this->_uniqueId = hash("sha1",implode("_",$parts));
		return $this->_uniqueId;
	}
}