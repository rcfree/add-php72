<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_PredictedQmvc_AjaxTest extends ADD_Test_EncounterBase {
	function setUp() {
		parent::setUp();
		$this->setupAjaxER();
                $em = ADD::sys()->getEncounterManager();
		$this->_cer = $this->setupAjaxER();
                $this->_epdCer = $this->setupAjaxER();
                
                $generic = $this->_module->getPage('generic');
		$generic->setupComponents();
                
                $epd = $this->_module->getPage('expulmdis');
		$epd->setupComponents();
                
		$em->setExpected($generic,"child",$this->_cer);
                $em->setExpected($epd,"child",$this->_epdCer);
	}	
	
	function testAjaxOutput() {
		$comp = $this->_page->getComponent('PredictedQmvc');
		$inputs = $comp->ajaxInputs($this->_page);
		$this->assertEquals(array(),$inputs);
	}
	
	function testAjaxFunctionWithEmptyValues() {
		$page = $this->_page;
		$comp = $page->getComponent('PredictedQmvc');
		
		$values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Date of assessment' in 'Generic info' page to populate % predicted QMVC",
			"Please enter 'Height','Total lean body mass' in 'E/P disease' page to populate % predicted QMVC"
		));
	}
	function testAjaxFunctionWithMissingValues() {
		$page = $this->_page;
		
		$map = array(array("assessment_date","20/01/2013"));
                $this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		
                $emap = array(array("height",180));
                $this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($emap));
		
		$comp = $page->getComponent('PredictedQmvc');
		
                $values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter 'Total lean body mass' in 'E/P disease' page to populate % predicted QMVC"
		));
	}
	
	function testAjaxFunctionWithAllValuesAndMissingGender() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		
                $emap = array(array("height",180),array("tot_lean_body_mass",3));
                $this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($emap));
		
                $comp = $page->getComponent('PredictedQmvc');
			
		$values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array(
			"Gender is not Male or Female. Unable to calculate predicted lung function values."
		));
	}
	
	
	function testAjaxFunctionWithAllValuesAndGender() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		
                $emap = array(array("height",180),array("tot_lean_body_mass",3));
                $this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($emap));
		
		$comp = $page->getComponent('PredictedQmvc');
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
		
		$values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>"9.68"),$output);
		$this->assertHasNoWarnings();
	}
	
	function testAjaxFunctionWithInvalidDob() {
		$page = $this->_page;
		$map = array(array("assessment_date","20/01/2013"));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		
                $emap = array(array("height",180),array("tot_lean_body_mass",3));
                $this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($emap));
                
		$comp = $page->getComponent('PredictedQmvc');
		ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"2001-03-04")));
		
		$values = array();
		$output = $comp->ajaxFunction($values,$page);
		$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array("Age could not be calculated. There may be a problem with the date of birth value."));
	}
        
        function testAjaxFunctionWithRepeatSectionRecord() {
            $page = $this->_page;
            $em = ADD::sys()->getEncounterManager();
            
            $er =$this->getERMock();
            $er->expects($this->any())->method("exists")->will($this->returnValue(true));

            $er->expects($this->any())->method("getEncounter")->will($this->returnValue(array("id"=>99)));
            $cer =$this->getERMock();
            $cer->expects($this->any())->method("exists")->will($this->returnValue(true));

            $cer->expects($this->any())->method("getEncounter")->will($this->returnValue(array("id"=>2)));
            
            $em->setExpected($page->getParent(),"child",$er);
            $generic = $this->_module->getPage('generic');
            $em->setExpected($generic,"child",$cer,$er);
            
            $epd = $this->_module->getPage('expulmdis');
            $em->setExpected($epd,"child",$cer,$er);
            
            $map = array(array("assessment_date","20/01/2013"),array("height",180),array("tot_lean_body_mass",3));
            $cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
            $comp = $page->getComponent('PredictedQmvc');
            ADD::db('patient')->expects($this->once())->method("getPatient")->will($this->returnValue(array("gender"=>"M","date_of_birth"=>"02/03/1945")));
            $generic = $this->_module->getPage('generic');
            $generic->setupComponents();
            $values = array();
            $output = $comp->ajaxFunction($values,$page);
            $this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>"9.68"),$output);
      	}
}
?>