<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Service\Remote;
use ClinicLE\Service\Remote\Access;
use Guzzle\Http\Message\Response;

class AccessTest extends \PHPUnit_Framework_TestCase {
	function setUp() {
		$this->client = $this->getMockBuilder("Guzzle\Http\Client")->disableOriginalConstructor()->getMock();
		$this->request = $this->getMockBuilder("Guzzle\Http\Message\Request")->disableOriginalConstructor()->getMock();
		$this->response = $this->getMockBuilder("Guzzle\Http\Message\Response")->disableOriginalConstructor()->getMock();
		$this->access = new Access($this->client,"345345345");
	}
	
	/**
	 * @test
	 */
	function it_should_get_a_patient_which_exists() {
		$this->expectWebServiceCallToOutputData("raw_patient.json");
		
		$patient = $this->access->getPatientWithIdentifier("S0000001");
		$this->assertEquals($patient,$this->loadJsonData("output_patient.json",true));
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_a_patient_does_not_exist() {
		$this->expectWebServiceCallToOutputData("missing_patient.json");
		$this->setExpectedException("ClinicLE\Service\Remote\Exception\RemoteDataException");
		$this->access->getPatientWithIdentifier("Z0000001");
	}
	
	/**
	 * @test
	 */
	function it_should_get_encounters_if_the_pim_is_present() {
		$this->expectWebServiceCallToOutputData("raw_wardspells.json");
		
		$encounters = $this->access->getEncountersUsingPim(92,"cdbrkr:sdetails");
		$this->assertEquals($encounters,$this->loadJsonData("output_wardspells.json",true));
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_the_pim_is_not_found_in_the_system() {
		$this->expectWebServiceCallToOutputData("missing_pim.json");
		$this->setExpectedException("ClinicLE\Service\Remote\Exception\RemoteDataException",("Module 'inv_lf' does not exist"));
		$this->access->getEncountersUsingPim(92,"inv_lf:test");
	}

	function loadJsonData($dataFile, $decodeJson=false) {
		$jsonContent = file_get_contents("data/".$dataFile);
		if($decodeJson) {
			return json_decode($jsonContent,true);
		}
		return $jsonContent;
	}
	
	function expectWebServiceCallToOutputData($dataFile) {
		$this->client->expects($this->once())->method("get")->will($this->returnValue($this->request));
		$this->request->expects($this->once())->method("getResponse")->will($this->returnValue($this->response));
		$this->response->expects($this->once())->method("getBody")->with(true)->will($this->returnValue($this->loadJsonData($dataFile)));
	}
}