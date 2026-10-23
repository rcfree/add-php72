<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_FraminghamRisk_AjaxTest extends ADD_Test_EncounterBase {
    public $_epdCer = null;
    function setUp() {
		parent::setUp();
		$this->_cer = $this->setupAjaxER();
                $this->_epdCer = $this->setupAjaxER();
                
                $em = ADD::sys()->getEncounterManager();
                
                $generic = $this->_module->getPage('generic');
		$generic->setupComponents();
                $em->setExpected($generic,"child",$this->_cer);
                
                $epd = $this->_module->getPage('expulmdis');
		$epd->setupComponents();
		$em->setExpected($epd,"child",$this->_epdCer);
                
                
	}	
	
	function testAjaxOutput() {
		
		$comp = $this->_page->getComponent('FraminghamRisk');
		$inputs = $comp->ajaxInputs($this->_page);
		$this->assertEquals(array('total_cholesterol', 'hdl_cholesterol', 'systolic_bp', 'treated_for_hypertension'),$inputs);
	}
	
	function testAjaxFunctionWithEmptyValues() {
                $em = ADD::sys()->getEncounterManager();
                
		$page = $this->_page;
		
		$comp = $page->getComponent('FraminghamRisk');
		
		$values = array("total_cholesterol"=>"");
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>null,"framingham_chd10yr_risk"=>null),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Date of assessment','Smoking history' in 'Generic info' page to populate Framingham risk score",
			"Please enter 'Total cholesterol','HDL cholesterol','Systolic BP','Treated for hypertension?' in this page to populate Framingham risk score"
		));
	}
	function testAjaxFunctionWithMissingValues() {
		$page = $this->_page;
		
		$map = array(array("assessment_date","20/01/2013"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('FraminghamRisk');
                $values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>null,"framingham_chd10yr_risk"=>null),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Smoking history' in 'Generic info' page to populate Framingham risk score",
			"Please enter 'Total cholesterol','HDL cholesterol','Systolic BP','Treated for hypertension?' in this page to populate Framingham risk score"
		));
	}
	
	function testAjaxFunctionWithAllValuesAndMissingGender() {
		$page = $this->_page;
                $map = array(array("assessment_date","20/01/2013"),array("smoking_history","2"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('FraminghamRisk');
		
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"","date_of_birth"=>"02/03/1945")));
		
		$values = array("total_cholesterol"=>"200","hdl_cholesterol"=>"80","systolic_bp"=>60,"treated_for_hypertension"=>"N");
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>null,"framingham_chd10yr_risk"=>null),$output);
		$this->assertHasWarnings(array(
			"Gender is not Male or Female. Unable to calculate predicted lung function values."
		));
	}
	
	
	function testAjaxFunctionWithAllValuesAndGender() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"),array("smoking_history","2"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('FraminghamRisk');
		
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
		
                $values = array("total_cholesterol"=>"200","hdl_cholesterol"=>"80","systolic_bp"=>60,"treated_for_hypertension"=>"N");
		
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>"11","framingham_chd10yr_risk"=>"8%"),$output);
		$this->assertHasNoWarnings();
	}
        
        function testAjaxFunctionWithExistingEncounterRecord() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"),array("smoking_history","2"));
		
                $this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $emap = array(array("total_cholesterol","60"),array("hdl_cholesterol","40"),array("systolic_bp",40),array("treated_for_hypertension","N"));
                $this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($emap));
		$comp = $page->getComponent('FraminghamRisk');
		
                ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
		
                $values = array("total_cholesterol"=>"200","hdl_cholesterol"=>"80","systolic_bp"=>60,"treated_for_hypertension"=>"N");
		
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>"11","framingham_chd10yr_risk"=>"8%"),$output);
		$this->assertHasNoWarnings();
	}
        
        
	
	function testAjaxFunctionWithInvalidDob() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"),array("smoking_history","2"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$comp = $page->getComponent('FraminghamRisk');
		
                ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"2001-03-04")));
		
                $values = array("total_cholesterol"=>"200","hdl_cholesterol"=>"80","systolic_bp"=>60,"treated_for_hypertension"=>"N");
		
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array("framingham_score"=>null,"framingham_chd10yr_risk"=>null),$output);
		$this->assertHasWarnings(array("Age could not be calculated. There may be a problem with the date of birth value."));
	}
        
}

?>