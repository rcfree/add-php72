<?php

class Component_LungFunction_AjaxTest extends ADD_Test_AjaxBase {
	const HEIGHTPAGE="heightpage";
	
	function getAjaxFunctionToTest() {
		return "LungFunction";
	}
	function getAjaxSourcePage() {
		return "lfpage";
	}
	function getAjaxSourceModule() {
		return "lfmod";
	}
	function setUp() {
		parent::setUp();
        $page = $this->_page;
		$this->_cer = $this->setupAjaxER();
        $em = ADD::sys()->getEncounterManager();
		$tpl = array(
			"lfmod"=>array("encounter"=>"lfmod","components"=>"Encounter_Domain"),
			"lfpage"=>array(
				"parent"=>"lfmod",
				"encounter"=>"lfpage",
				"components"=>"Form_Simple Encounter_Single",
				"LungFunction.height_page"=>"lfheight",
				"LungFunction.height_field"=>"height",
				"LungFunction.date_page"=>"lfpage",
				"LungFunction.date_field"=>"spirometry_date",
				"LungFunction.pred_fev1"=>"pred_fev1",
				"LungFunction.pred_fvc"=>"pred_fvc",
				"LungFunction.pred_fev1_fvc"=>"pred_fev1_fvc",
				"LungFunction.pred_pef"=>"pred_pef",
				),
			"lfheight"=>array("parent"=>"lfmod","title"=>"height page","encounter"=>"lfpage","components"=>"Form_Simple Encounter_Single")
			);
		$modConfig = new ADD_Config($tpl);
		$this->lfModule = new ADD_Module("lfmod",$modConfig);
		$this->lfModule->initPages(new ADD_PageCollection($this->lfModule));
		$heightPage = $this->lfModule->getPage('lfheight');
		$heightForm = $heightPage->getComponent('Form');
		$heightForm->addField(new ADD_Field_Text_Number(array("Field"=>"height","Level"=>1,"Title"=>"Height")));
		
		$mainPage = $this->lfModule->getPage('lfpage');
		$mainForm = $mainPage->getComponent('Form');
		$mainForm->addField(new ADD_Field_Text_Number(array("Field"=>"spirometry_date","Level"=>1,"Title"=>"Spirometry date")));
		
		
		ADD::sys()->getModules()->add($this->lfModule);
		
		$er = $this->getMock('ADD_EncounterRecord');
		$em->setExpected("lfmod","record",$er);
		$em->setExpected("lfheight","child",$this->_cer,$er);
        $em->setExpected("lfpage","child",$this->_cer,$er);
	}	
	
	function testAjaxOutput() {
		$comp = $this->_page->getComponent('LungFunction');
		$inputs = $comp->ajaxInputs($this->_page);
		$this->assertEquals(array("spirometry_date","cfactor_90pc","height"),$inputs);
	}
	
	function testAjaxFunctionWithEmptyValues() {
		$page = $this->_page;
		$this->assertAjaxOutputEquals(array(),
		array(
				"outputs"=>array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),
				"messages"=>array(
					"warnings"=>array(
						"Please enter 'Height' in 'height page' page to calculate predicted lung function values",
						"Please enter 'Spirometry date' in this page to calculate predicted lung function values"
					)
				),
				"fields"=>array("spirometry_date","cfactor_90pc","height")
			)
		);
	}
	
	function testAjaxFunctionWithMissingValues() {
		$map = array();
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$values = array("spirometry_date"=>"05/02/2013");
		
		$this->assertAjaxOutputEquals($values,
		array(
				"outputs"=>array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),
				"messages"=>array(
					"warnings"=>array(
						"Please enter 'Height' in 'height page' page to calculate predicted lung function values",
					)
				),
				"fields"=>array("spirometry_date","cfactor_90pc","height")
			)
		);
	}
	
	function testAjaxFunctionWithAllValuesAndMissingGender() {
		$map = array( array("height",160), array("spirometry_date","20/01/2013" ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
			

		$values = array("spirometry_date"=>"05/02/2013");
	
		$this->assertAjaxOutputEquals($values,
		array(
				"outputs"=>array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),
				"messages"=>array(
					"warnings"=>array(
						"Gender is not Male or Female. Unable to calculate predicted lung function values.",
					)
				),
				"fields"=>array("spirometry_date","cfactor_90pc","height")
			)
		);
		
	}
	
	function testAjaxFunctionWithAllValuesAndGender() {
		$page = $this->_page;
		$map = array( array("height",160), array("spirometry_date","20/01/2013" ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
		$values = array("spirometry_date"=>"05/02/2013");
		
		$this->assertAjaxOutputEquals($values,
		array(
				"outputs"=>array("pred_fev1"=>2.45,"pred_fvc"=>3.13,"pred_fev1_fvc"=>75.15,"pred_pef"=>425.58),
				"messages"=>array(),
				"fields"=>array("spirometry_date","cfactor_90pc","height")
			)
		);
	}
	
	function testAjaxFunctionWithInvalidDob() {
		$page = $this->_page;
		$map = array( array("height",160), array( ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"2001-03-04")));

		$values = array("spirometry_date"=>"05/02/2013");
	
		$this->assertAjaxOutputEquals($values,
		array(
				"outputs"=>array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),
				"messages"=>array(
					"warnings"=>array("Age could not be calculated. There may be a problem with the date of birth value.")
				),
				"fields"=>array("spirometry_date","cfactor_90pc","height")
			)
		);
	}
	
}
?>