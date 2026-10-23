<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
require_once("ADD/PatientImport/MatchResult.php");
require_once("ADD/PatientImport/MatchResultFactory.php");
require_once("ADD/PatientImport/PmiMatcher.php");
require_once("LRB/PatientLookup.php");
class ADD_Import_PmiMatcherTest extends ADD_BaseTestFixture {
	public $pmi;
	public $matchResultFactory;
	
	function setUp() {
		$this->pmi = $this->getMock("LRB_PatientLookup");
		$this->matchResultFactory = $this->getMock("ADD_PatientImport_MatchResultFactory");
		$this->matchResult = $this->getMockBuilder("ADD_PatientImport_MatchResult")->disableOriginalConstructor()->setMethods(array("attemptFullMatch","zeroScores","isNoNameMatch"))->getMock();
		$this->matchResultFactory->expects($this->once())->method("createWithSource")->will($this->returnValue($this->matchResult));
		
		$this->SUT = new ADD_PatientImport_PmiMatcher($this->pmi, $this->matchResultFactory);
	}
	
	/**
	 * @test
	 */
	function it_should_not_attempt_to_get_pmi_patient_if_no_id_or_name() {
		$sourcePatient = array("identifier"=>null);
		$this->matchResult->setSourcePatient($sourcePatient);
		
		$this->matchResult->expects($this->never())->method("getPmiPatient");
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->matchResult->expects($this->once())->method("zeroScores");
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("Patient identifier is null;Patient first name missing;Patient last name missing;Patient date of birth missing",$this->matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_should_only_get_pmi_patient_by_id_if_provided_and_names_match() {
		$sourcePatient = array("identifier"=>"S0000007");
		$this->matchResult->setSourcePatient($sourcePatient);
		$pmiPatient = array("main_pat_id"=>"S0000007");
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S0000007")->will($this->returnValue($pmiPatient));
		$this->pmi -> expects($this->never())->method("get_patient_by_info");
		
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(false));
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("",$this->matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function fails_to_get_patient_if_id_not_found_and_no_names_or_dob() {
		$sourcePatient = array("identifier"=>"S0000007");
		$this->matchResult->setSourcePatient($sourcePatient);
		$pmiPatient = array("main_pat_id"=>"S0000007");
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S0000007")->will($this->returnValue(null));
		$this->pmi -> expects($this->never())->method("get_patient_records");
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("Patient identifier 'S0000007' not found in DB;Patient first name missing;Patient last name missing;Patient date of birth missing",$this->matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function fails_to_get_patient_if_id_not_found_and_names_but_no_dob() {
		$sourcePatient = array("identifier"=>"S0000007","first_name"=>"JAMES","last_name"=>"BOND");
		$this->matchResult->setSourcePatient($sourcePatient);
		$pmiPatient = array("main_pat_id"=>"S0000007");
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S0000007")->will($this->returnValue(null));
		$this->pmi -> expects($this->never())->method("get_patient_records");
		
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("Patient identifier 'S0000007' not found in DB;Patient date of birth missing",$this->matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function gets_existing_patient_if_id_not_found_in_pmi_but_full_names_and_dob_return_pmi_patient() {
		$sourcePatient = array("identifier"=>"S","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$this->matchResult->setSourcePatient($sourcePatient);
		$pmiPatient = array("main_pat_id"=>"S0000007","first_name"=>"JAMES","last_name"=>"BOND");
		$pmiExpected = array(array("Title"),array($pmiPatient,array()));
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S")->will($this->returnValue(null));
		$this->pmi -> expects($this->once())->method("get_patient_records")->with(array("fname"=>"JAMES","lname"=>"BOND","dob"=>"03/02/1956"))->will($this->returnValue($pmiExpected));
		
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->SUT->determineMatchResult($sourcePatient);
		//$this->assertEquals("Patient identifier 'S' not found in DB",$this->matchResult->getIssuesAsText());
		$this->assertEquals($pmiPatient,$this->matchResult->getPmiPatient());
	}
	
	/**
	 * @test
	 */
	function fail_if_no_patient_found_in_pmi_with_names_and_dob() {
		$sourcePatient = array("identifier"=>"S0000007","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("main_pat_id"=>"S0000007","first_name"=>"JAMES","last_name"=>"BOND");
		$pmiExpected = array(array("Title"));
		
		$this->matchResult->setSourcePatient($sourcePatient);
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S0000007")->will($this->returnValue(null));
		$this->pmi -> expects($this->once())->method("get_patient_records")->will($this->returnValue($pmiExpected));
		
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("Patient identifier 'S0000007' not found in DB;Patient matching info ".json_encode($sourcePatient)." not found in DB",$this->matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function fail_if_multiple_patients_found_in_pmi_with_same_names_and_dob() {
		$sourcePatient = array("identifier"=>"S0000007","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("main_pat_id"=>"S0000007");
		$pmiExpected = array(array("Title"),array($pmiPatient,array()),array($pmiPatient,array()));
		$this->matchResult->setSourcePatient($sourcePatient);
		$this->pmi -> expects($this->once())->method("get_patient_by_id")->with("S0000007")->will($this->returnValue(null));
		$this->pmi -> expects($this->once())->method("get_patient_records")->will($this->returnValue($pmiExpected));
		
		$this->matchResult->expects($this->once())->method("isNoNameMatch")->will($this->returnValue(true));
		$this->SUT->determineMatchResult($sourcePatient);
		$this->assertEquals("Patient identifier 'S0000007' not found in DB;Multiple patients found in DB matching info ".json_encode($sourcePatient).". Patients:".json_encode(array($pmiPatient,$pmiPatient)),$this->matchResult->getIssuesAsText());
	}
}
