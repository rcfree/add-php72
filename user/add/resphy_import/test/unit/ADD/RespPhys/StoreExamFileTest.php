<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_RespPhys_StoreExamFileTest extends PHPUnit_Framework_TestCase {
   
    function setUp() {
		$this->pmiMatcher = $this->getMockBuilder("ADD_PatientImport_PmiMatcher")->disableOriginalConstructor()->getMock();
		$this->dataSource = $this->getMock("ADD_RespPhys_DataSource");
		$this->examFile = $this->getMockBuilder("ADD_RespPhys_ExamFile")->disableOriginalConstructor()->getMock();
		$this->matchResult = $this->getMockBuilder("ADD_PatientImport_MatchResult")->disableOriginalConstructor()->getMock();
		$this->SUT = new ADD_RespPhys_StoreExamFile($this->pmiMatcher,$this->dataSource);
    }
	
	/**
	 * @test
	 */
	function it_should_add_the_exam_to_holding_if_patient_match_score_is_not_15() {
		$this->pmiMatcher->expects($this->once())->method("determineMatchResult")->will($this->returnValue($this->matchResult));
		$this->matchResult->expects($this->once())->method("getTotalScore")->will($this->returnValue(14));
		$this->dataSource->expects($this->once())->method("addToHolding");
		$this->dataSource->expects($this->never())->method("addToPatient");
		$this->SUT->store($this->examFile);
	}
	
	/**
	 * @test
	 */
	function it_should_add_the_exam_to_a_patient_if_patient_match_score_is_15() {
		$this->pmiMatcher->expects($this->once())->method("determineMatchResult")->will($this->returnValue($this->matchResult));
		$this->matchResult->expects($this->once())->method("getTotalScore")->will($this->returnValue(15));
		$this->dataSource->expects($this->never())->method("addToHolding");
		$this->dataSource->expects($this->once())->method("addToPatient");
		$this->SUT->store($this->examFile);
	}
}