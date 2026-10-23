<?php
set_include_path(getenv('ADD_PATH')."/test/module");

require_once("Component/EncounterBase.php");
class Component_LungFunction_AjaxTest extends Component_EncounterBase {
	function setUp() {
		parent::setUp();
                $page = $this->_page;
		$this->_cer = $this->setupAjaxER();
                $em = ADD::sys()->getEncounterManager();
                $dets = $this->_module->getPage($page->getSetting('LungFunction.height_page'));
		$em->setExpected($dets->getSetting('encounter'),"child",$this->_cer);
                $em->setExpected($page->getSetting('encounter'),"child",$this->_cer);
	}	
	
	function testAjaxOutput() {
		$comp = $this->_page->getComponent('LungFunction');
		$inputs = array();
		$comp->ajaxInputs($inputs,$this->_page);
		$this->assertEquals(array("spirometry_date","cfactor_90pc"),$inputs['lungfunction']);
	}
	
	function testAjaxFunctionWithEmptyValues() {
		$page = $this->_page;
		
		$comp = $page->getComponent('LungFunction');
		$funcs = array();
			
		$dets = $this->_module->getPage($page->getSetting('LungFunction.height_page'));
		$dets->setupComponents();
		$comp->ajaxFunctions($funcs,$page);
		$values = array();
		$output = $funcs['lungfunction']($page,$values);
		$this->assertEquals(array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Height' in '".$dets->getSetting('title')."' page to calculate predicted lung function values",
			"Please enter 'Spirometry date' in this page to calculate predicted lung function values"
		));
	}
	function testAjaxFunctionWithMissingValues() {
		$page = $this->_page;
		
		$map = array();
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('LungFunction');
		$funcs = array();
			
		$dets = $this->_module->getPage($page->getSetting('LungFunction.height_page'));
		$dets->setupComponents();
		$comp->ajaxFunctions($funcs,$page);
		$values = array("spirometry_date"=>"05/02/2013");
		$output = $funcs['lungfunction']($page,$values);
		$this->assertEquals(array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Height' in '".$dets->getSetting('title')."' page to calculate predicted lung function values",
		));
	}
	
	function testAjaxFunctionWithAllValuesAndMissingGender() {
		$page = $this->_page;
		$map = array( array("height",160), array("spirometry_date","20/01/2013" ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('LungFunction');
		$funcs = array();
			
		$dets = ADD::sys()->getPageInModule('dac','details');
		$dets->setupComponents();
		$comp->ajaxFunctions($funcs,$page);
		$values = array("spirometry_date"=>"05/02/2013");
		$output = $funcs['lungfunction']($page,$values);
		$this->assertEquals(array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),$output);
		$this->assertHasWarnings(array(
			"Gender is not Male or Female. Unable to calculate predicted lung function values."
		));
	}
	
	function testAjaxFunctionWithAllValuesAndGender() {
		$page = $this->_page;
		$map = array( array("height",160), array("spirometry_date","20/01/2013" ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('LungFunction');
		$funcs = array();
		self::$_pdb->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
		$dets = ADD::sys()->getPageInModule('dac','details');
		$dets->setupComponents();
		$comp->ajaxFunctions($funcs,$page);
		$values = array("spirometry_date"=>"05/02/2013");
		$output = $funcs['lungfunction']($page,$values);
		$this->assertEquals(array("pred_fev1"=>"2.45","pred_fvc"=>"3.13","pred_fev1_fvc"=>"75.15","pred_pef"=>"425.58"),$output);
		$this->assertHasNoWarnings();
	}
	
	function testAjaxFunctionWithInvalidDob() {
		$page = $this->_page;
		$map = array( array("height",160), array( ) );
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('LungFunction');
		$funcs = array();
		self::$_pdb->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"2001-03-04")));
		$dets = ADD::sys()->getPageInModule('dac','details');
		$dets->setupComponents();
		$comp->ajaxFunctions($funcs,$page);
		$values = array("spirometry_date"=>"05/02/2013");
		$output = $funcs['lungfunction']($page,$values);
		$this->assertEquals(array("pred_fev1"=>"","pred_fvc"=>"","pred_fev1_fvc"=>"","pred_pef"=>""),$output);
		$this->assertHasWarnings(array("Age could not be calculated. There may be a problem with the date of birth value."));
	}
	
}
?>