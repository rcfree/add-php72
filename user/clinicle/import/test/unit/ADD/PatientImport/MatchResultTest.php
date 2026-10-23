<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
require_once("ADD/PatientImport/MatchResult.php");

class ADD_Import_MatchResultTest extends ADD_BaseTestFixture {
	
	/**
	 * @test
	 */
	function it_scores_15_when_pmi_patient_matches_source_patient() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(15,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name match (pmi:JAMES, src:JAMES);Last name match (pmi:BOND, src:BOND)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_15_when_pmi_patient_matches_source_patient_but_case_is_different() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"James","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(15,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name match (pmi:JAMES, src:JAMES);Last name match (pmi:BOND, src:BOND)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_10_when_pmi_patient_name_matches_source_patient_but_dob_is_different() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"02/02/1956");
		$pmiPatient = array("first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(10,$matchResult->getTotalScore());
		$this->assertEquals(0,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
	
		$this->assertEquals("DOB not equal (pmi:03/02/1956, src:02/02/1956);First name match (pmi:JAMES, src:JAMES);Last name match (pmi:BOND, src:BOND)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_13_when_pmi_patient_names_are_swapped_compared_to_source_patient() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JAMES","last_name"=>"RICARDO","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"RICARDO","last_name"=>"JAMES","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(13,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(4,$matchResult->getLScore());
		$this->assertEquals(4,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);Swapped first name match (pmi:RICARDO, src:RICARDO);Swapped last name match (pmi:JAMES, src:JAMES)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_10_when_pmi_patient_names_are_similar_to_source_patient() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JOHN","last_name"=>"RICARDS","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JOHN","last_name"=>"RICHARD","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(13,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(3,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name match (pmi:JOHN, src:JOHN);Last name fuzzy match [l-score <=2] (pmi:RICHARD, src:RICARDS)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_13_when_pmi_patient_matches_with_source_patient_initial() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"J","last_name"=>"RICHARD","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JOHN","last_name"=>"RICHARD","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(13,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(3,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name initial matches (pmi:JOHN, src:J);Last name match (pmi:RICHARD, src:RICHARD)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_0_when_pmi_patient_does_not_matches_with_patient_at_all() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"ANDY","last_name"=>"PANDY","dob"=>"04/02/1964");
		$pmiPatient = array("first_name"=>"JOHN","last_name"=>"RICHARD","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(0,$matchResult->getTotalScore());
		$this->assertEquals(0,$matchResult->getDScore());
		$this->assertEquals(0,$matchResult->getLScore());
		$this->assertEquals(0,$matchResult->getFScore());
		$this->assertEquals("DOB not equal (pmi:03/02/1956, src:04/02/1964);First name does not match (pmi:JOHN, src:ANDY OR PANDY;Last name does not match (pmi:RICHARD, src:PANDY OR ANDY",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_12_when_pmi_patient_names_match_but_source_dob_is_empty() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JAMES","last_name"=>"BOND","dob"=>"");
		$pmiPatient = array("first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(12,$matchResult->getTotalScore());
		$this->assertEquals(2,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
		$this->assertEquals("Source DOB empty (pmi:03/02/1956);First name match (pmi:JAMES, src:JAMES);Last name match (pmi:BOND, src:BOND)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_10_when_pmi_patient_names_fuzzy_match_but_dob_match() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"JONNY","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JOHN","last_name"=>"BARD","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(11,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(3,$matchResult->getLScore());
		$this->assertEquals(3,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name fuzzy match [l-score <=2] (pmi:JOHN, src:JONNY);Last name fuzzy match [l-score <=2] (pmi:BARD, src:BOND)",$matchResult->getIssuesAsText());
	}
	
	/**
	 * @test
	 */
	function it_scores_15_when_pmi_patient_name_matches_source_patient_segment_of_name() {
		$sourcePatient = array("identifier"=>"S1234567","first_name"=>"James Lance","last_name"=>"BOND","dob"=>"03/02/1956");
		$pmiPatient = array("first_name"=>"JAMES","last_name"=>"BOND","dob"=>"03/02/1956");
		$matchResult = new ADD_PatientImport_MatchResult($sourcePatient);
		$matchResult->setPmiPatient($pmiPatient);
		$matchResult->attemptFullMatch();
		$this->assertEquals(15,$matchResult->getTotalScore());
		$this->assertEquals(5,$matchResult->getDScore());
		$this->assertEquals(5,$matchResult->getLScore());
		$this->assertEquals(5,$matchResult->getFScore());
		$this->assertEquals("DOB match (pmi:03/02/1956, src:03/02/1956);First name portion matches (pmi:JAMES, src:JAMES LANCE);Last name match (pmi:BOND, src:BOND)",$matchResult->getIssuesAsText());
	}
}
