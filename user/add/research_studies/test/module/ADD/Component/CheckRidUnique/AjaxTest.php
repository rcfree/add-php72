<?php

class Component_CheckRidUnique_AjaxTest extends ADD_Test_AjaxBase {
	const STUDYNO1 = "123456";
	const STUDYNO2 = "234567";
	const STUDY=1;
	function getAjaxFunctionToTest() {
		return "CheckRidUnique";
	}
	function getAjaxSourcePage() {
		return "sdetails";
	}
	function getAjaxSourceModule() {
		return "research_studies";
	}
	/**
	 * @test
	 */
	function expected_inputs_returned() {
		$comp = $this->_page->getComponent('CheckRidUnique');
		$inputs = $comp->ajaxInputs($this->_page);
		$this->assertEquals(array("study_no"),$inputs);
	}
	/**
	 * @test
	 */
	function returns_no_messages_if_no_study_no_supplied() {
		$page = $this->_page;
		$this->assertAjaxOutputEquals(array(),
		array(
				"outputs"=>null,
				"messages"=>array(),
				"fields"=>array("study_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function returns_error_if_no_study_selected() {
		$page = $this->_page;
		$this->assertAjaxOutputEquals(array("study_no"=>self::STUDYNO1),
		array(
				"outputs"=>null,
				"messages"=>array(
					"errors"=>array(
						array("fieldTitle"=>"Unexpected error","message"=>"Study type must be selected")
					)
				),
				"fields"=>array("study_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function returns_no_message_if_study_number_does_not_exist() {
		$encs = array(
			array("id"=>1),
			array("id"=>2),
			array("id"=>3)
		);
		ADD::db('encounter')->expects($this->once())->method("getEncountersWithObservationValue")->will($this->returnValue($encs));
		$obs = array(
			array($encs[0],false,array("study_no"=>self::STUDYNO2))
		);
		ADD::db('encounter')->expects($this->exactly(3))->method("getObservationsInEncounter")->will($this->returnValueMap($obs));
		
		$page = $this->_page;
		$this->assertAjaxOutputEquals(array("study_no"=>self::STUDYNO1,"study"=>1),
		array(
				"outputs"=>null,
				"messages"=>array(),
				"fields"=>array("study_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function returns_error_if_study_number_exists_in_study() {
		$page = $this->_page;
		$encs = array(
			array("id"=>1),
			array("id"=>2),
			array("id"=>3)
		);
		ADD::db('encounter')->expects($this->once())->method("getEncountersWithObservationValue")->will($this->returnValue($encs));
		$obs = array(
			array($encs[0],false,array("study_no"=>self::STUDYNO1))
		);
		ADD::db('encounter')->expects($this->exactly(3))->method("getObservationsInEncounter")->will($this->returnValueMap($obs));
		
		$this->assertAjaxOutputEquals(array("study_no"=>self::STUDYNO1,"study"=>1),
		array(
				"outputs"=>null,
				"messages"=>array("errors"=>array(
					array("fieldTitle"=>"Study number","fieldName"=>"study_no", "message"=>"<i>".self::STUDYNO1."</i> has already been used <b>1</b> time in this study"))),
				"fields"=>array("study_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function returns_no_message_if_no_study_records() {
		$encs = null;
		ADD::db('encounter')->expects($this->once())->method("getEncountersWithObservationValue")->will($this->returnValue(null));
		ADD::db('encounter')->expects($this->never())->method("getObservationsInEncounter");
		
		$page = $this->_page;
		$this->assertAjaxOutputEquals(array("study_no"=>self::STUDYNO1,"study"=>1),
		array(
				"outputs"=>null,
				"messages"=>array(),
				"fields"=>array("study_no")
			)
		);
	}
}
?>