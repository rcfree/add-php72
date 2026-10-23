<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Acute;
use ClinicLE\Service\Remote\Access;
use ClinicLE\Service\Remote\Exception\RemoteDataException;

class SpellCollateTest extends \PHPUnit_Framework_TestCase {
	const IDENTIFIER="S1234678";
	const PID = 123;
	
	private $spellCollate;
	private $access;
	
	public function setUp() {
		$this->access = $this->getMockBuilder("ClinicLE\Service\Remote\Access")->disableOriginalConstructor()->getMock();
		$this->spellCollate = new SpellCollate($this->access);
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_the_patient_does_not_exist() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->throwException(new RemoteDataException()));
		$this->setExpectedException("ClinicLE\Acute\Exception\DataRetrieveException","Sorry unable to retrieve patient from Codebreaker");
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
	}
	
	/**
	 * @test
	 */
	function it_should_return_an_empty_list_if_the_patient_exists_but_has_no_spells() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->once())->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue(array()));
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
		$this->assertEquals(array(),$spells);
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_the_patient_has_no_id() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("identifier"=>"s123456")));
		$this->setExpectedException("ClinicLE\Common\Exception\NullValueException","ID in Codebreaker patient is missing");
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
	}
	
	
	/**
	 * @test
	 */
	function it_should_fail_if_a_spell_has_no_parent_id() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$spells = array(
			array("patient_id"=>self::PID,"is_verified"=>0)
		);
		$this->access->expects($this->at(1))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue($spells));
		$this->access->expects($this->at(2))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DIAGNOSES_PIM)->will($this->returnValue($spells));
		
		$this->setExpectedException("ClinicLE\Common\Exception\NullValueException","ID in Codebreaker spell diagnosis is missing");
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
		
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_spells_cannot_be_retrieved() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->once())->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->throwException(new RemoteDataException()));
		$this->setExpectedException("ClinicLE\Acute\Exception\DataRetrieveException","Sorry unable to retrieve ward spells from Codebreaker");
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
		
	}
	
	/**
	 * @test
	 */
	function it_should_fail_if_diagnoses_cannot_be_retrieved() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->at(1))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue(array(array("patient_id"=>self::PID))));
		$this->access->expects($this->at(2))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DIAGNOSES_PIM)->will($this->throwException(new RemoteDataException()));
		
		$this->setExpectedException("ClinicLE\Acute\Exception\DataRetrieveException","Sorry unable to retrieve diagnoses from Codebreaker");
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);
		
	}
	
	const SPELL1="151458";
	const SPELL2="156907";
	const SPELL3="162759";
	
	/**
	 * @test
	 */
	function it_should_return_keyed_spells_if_the_patient_has_wardspells() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->at(1))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue($this->loadJsonData("output_wardspells.json",true)));
		$this->access->expects($this->at(2))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DIAGNOSES_PIM)->will($this->returnValue($this->loadJsonData("output_diagnoses.json",true)));
		
		$attrs1 = array("parentid"=>self::SPELL1,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(3))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs1)->will($this->returnValue($this->loadJsonData("output_items1.json",true)));
		
		$attrs2 = array("parentid"=>self::SPELL2,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(4))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs2)->will($this->returnValue($this->loadJsonData("output_items2.json",true)));
		
		$attrs3 = array("parentid"=>self::SPELL3,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(5))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs3)->will($this->returnValue($this->loadJsonData("output_items3.json",true)));
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);

		$this->assertEquals(array(
			self::SPELL1=>"10/02/2015 (FCDU) - E11 - Non-insulin-dependent diabetes mellitus",
			self::SPELL2=>"07/04/2015 (FCDU) - J47 - Bronchiectasis",
			self::SPELL3=>"03/07/2015 (FCDU) - J45.8 - Mixed asthma"
		),$spells);
	}
	
	/**
	 * @test
	 */
	function it_should_return_spells_if_they_have_no_primary_diagnosis() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->at(1))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue($this->loadJsonData("output_wardspells.json",true)));
		$this->access->expects($this->at(2))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DIAGNOSES_PIM)->will($this->returnValue($this->loadJsonData("output_diagnoses.json",true)));
		
		
		$attrs1 = array("parentid"=>self::SPELL1,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(3))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs1)->will($this->returnValue($this->loadJsonData("output_items1.json",true)));
		
		$attrs2 = array("parentid"=>self::SPELL2,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(4))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs2)->will($this->returnValue($this->loadJsonData("output_items_no_pri.json",true)));
		
		$attrs3 = array("parentid"=>self::SPELL3,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(5))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs3)->will($this->returnValue($this->loadJsonData("output_items3.json",true)));
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);

		$this->assertEquals(array(
			self::SPELL1=>"10/02/2015 (FCDU) - E11 - Non-insulin-dependent diabetes mellitus",
			self::SPELL2=>"07/04/2015 (FCDU) - No primary diagnosis",
			self::SPELL3=>"03/07/2015 (FCDU) - J45.8 - Mixed asthma"
		),$spells);
	}
	
	/**
	 * @test
	 */
	function it_should_return_unverified_spells_enclosed_with_brackets() {
		$this->access->expects($this->once())->method("getPatientWithIdentifier")->with(self::IDENTIFIER)->will($this->returnValue(array("id"=>self::PID)));
		$this->access->expects($this->at(1))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DETAILS_PIM)->will($this->returnValue($this->loadJsonData("output_wardspells.json",true)));
		$this->access->expects($this->at(2))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::DIAGNOSES_PIM)->will($this->returnValue($this->loadJsonData("output_diagnoses_unverified.json",true)));
		
		$attrs1 = array("parentid"=>self::SPELL1,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(3))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs1)->will($this->returnValue($this->loadJsonData("output_items1.json",true)));
		
		$attrs2 = array("parentid"=>self::SPELL2,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(4))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs2)->will($this->returnValue($this->loadJsonData("output_items_no_pri.json",true)));
		
		$attrs3 = array("parentid"=>self::SPELL3,"confirmparenttype"=>  SpellCollate::SPELL_TYPE);
		$this->access->expects($this->at(5))->method("getEncountersUsingPim")->with(self::PID,SpellCollate::ITEMS_PIM,$attrs3)->will($this->returnValue($this->loadJsonData("output_items3.json",true)));
		
		$spells = $this->spellCollate->collateSpells(self::IDENTIFIER);

		$this->assertEquals(array(
			self::SPELL1=>"10/02/2015 (FCDU) - E11 - Non-insulin-dependent diabetes mellitus",
			self::SPELL2=>"[07/04/2015 (FCDU) - No primary diagnosis]",
			self::SPELL3=>"03/07/2015 (FCDU) - J45.8 - Mixed asthma"
		),$spells);
	}
	
	
	function loadJsonData($dataFile, $decodeJson=false) {
		$jsonContent = file_get_contents("data/".$dataFile);
		if($decodeJson) {
			return json_decode($jsonContent,true);
		}
		return $jsonContent;
	}
	
}