<?php

class ADD_Component_PatientIdCheck_AjaxTest extends ADD_Test_AjaxBase {
	const PID1=1;
	const PID2=123;
	const PID3=999;
	const IDENTIFIER="S1234567";
	const NHS_NO="123 456 7890";
	
	function getAjaxFunctionToTest() {
		return "PatientIdCheck";
	}
	
	function getAjaxSourcePage() {
		return "new";
	}
	function getAjaxSourceModule() {
		return "patient";
	}
	
	/**
	 * @test
	 */
	function it_should_return_an_error_if_identifier_exists_and_is_not_current_patient() {
		$page = $this->_page;
		ADD::sys()->setPatientId(self::PID1);
		ADD::db('search_patient')->expects($this->once())->method("doPatientsExistWithIdentifiers")->will($this->returnValue(true));
		ADD::db('search_patient')->expects($this->once())->method("findExistingPatientIds")->will($this->returnValue(
				array(self::PID2=>array("identifier"))
		));
		
		$this->assertAjaxOutputEquals(array("identifier"=>self::IDENTIFIER),
		array(
				"outputs"=>null,
				"messages"=>array(
					"errors"=>array(
						array(
							"fieldTitle"=>"",
							"fieldName"=>"identifier",
							"message"=>"A patient with S number '".self::IDENTIFIER."' already exists in ADD.<br/><a href='/patient/demographics?pid=".self::PID2."'>Go to patient record in ADD</a>"
						)
					)
				),
				"fields"=>array("identifier","nhs_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function it_should_not_return_an_error_if_identifier_exists_but_is_current_patient() {
		$page = $this->_page;
		ADD::sys()->setPatientId(self::PID1);
		ADD::db('search_patient')->expects($this->once())->method("doPatientsExistWithIdentifiers")->will($this->returnValue(true));
		ADD::db('search_patient')->expects($this->once())->method("findExistingPatientIds")->will($this->returnValue(
				array()
		));
		
		$this->assertAjaxOutputEquals(array("identifier"=>self::IDENTIFIER),
		array(
				"outputs"=>null,
				"messages"=>array(),
				"fields"=>array("identifier","nhs_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function it_should_return_an_error_if_nhs_number_exists_and_is_not_current_patient() {
		$page = $this->_page;
		ADD::sys()->setPatientId(self::PID1);
		ADD::db('search_patient')->expects($this->once())->method("doPatientsExistWithIdentifiers")->will($this->returnValue(true));
		ADD::db('search_patient')->expects($this->once())->method("findExistingPatientIds")->will($this->returnValue(
				array(self::PID2=>array("nhs_no"))
		));
		
		$this->assertAjaxOutputEquals(array("nhs_no"=>self::NHS_NO),
		array(
				"outputs"=>null,
				"messages"=>array(
					"errors"=>array(
						array(
							"fieldTitle"=>"",
							"fieldName"=>"nhs_no",
							"message"=>"A patient with NHS number '".self::NHS_NO."' already exists in ADD.<br/><a href='/patient/demographics?pid=".self::PID2."'>Go to patient record in ADD</a>"
						)
					)
				),
				"fields"=>array("identifier","nhs_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function it_should_return_an_error_if_both_identifier_and_nhs_number_exist_and_are_not_current_patient() {
		$page = $this->_page;
		ADD::sys()->setPatientId(self::PID1);
		ADD::db('search_patient')->expects($this->once())->method("doPatientsExistWithIdentifiers")->will($this->returnValue(true));
		ADD::db('search_patient')->expects($this->once())->method("findExistingPatientIds")->will($this->returnValue(
				array(self::PID2=>array("nhs_no","identifier"))
		));
		
		$this->assertAjaxOutputEquals(array("nhs_no"=>self::NHS_NO,"identifier"=>self::IDENTIFIER),
		array(
				"outputs"=>null,
				"messages"=>array(
					"errors"=>array(
						array(
							"fieldTitle"=>"",
							"fieldName"=>"identifier",
							"message"=>"A patient with S number '".self::IDENTIFIER."' and NHS number '".self::NHS_NO."' already exists in ADD.<br/><a href='/patient/demographics?pid=".self::PID2."'>Go to patient record in ADD</a>"
						)
					)
				),
				"fields"=>array("identifier","nhs_no")
			)
		);
	}
	
	/**
	 * @test
	 */
	function it_should_return_the_first_error_if_both_identifier_and_nhs_number_exist_but_are_different_patients() {
		$page = $this->_page;
		ADD::sys()->setPatientId(self::PID1);
		ADD::db('search_patient')->expects($this->once())->method("doPatientsExistWithIdentifiers")->will($this->returnValue(true));
		ADD::db('search_patient')->expects($this->once())->method("findExistingPatientIds")->will($this->returnValue(
				array(self::PID3=>array("nhs_no"),self::PID2=>array("identifier"))
		));
		
		$this->assertAjaxOutputEquals(array("nhs_no"=>self::NHS_NO,"identifier"=>self::IDENTIFIER),
		array(
				"outputs"=>null,
				"messages"=>array(
					"errors"=>array(
						array(
							"fieldTitle"=>"",
							"fieldName"=>"nhs_no",
							"message"=>"A patient with NHS number '".self::NHS_NO."' already exists in ADD.<br/><a href='/patient/demographics?pid=".self::PID3."'>Go to patient record in ADD</a>"
						)
					)
				),
				"fields"=>array("identifier","nhs_no")
			)
		);
	}
}
?>