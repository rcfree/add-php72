<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_IbodeScore_ValueTest extends ADD_Test_EncounterBase {
    private $_field = null;
	function setUp() {
            parent::setUp();
            $this->setupAjaxER();
            $em = ADD::sys()->getEncounterManager();
            $this->_cer = $this->setupAjaxER();
            $this->_epdCer = $this->setupAjaxER();

            $exercise = $this->_module->getPage('exercise');
            $exercise->setupComponents();

            $epd = $this->_module->getPage('expulmdis');
            $epd->setupComponents();

            $em->setExpected($exercise,"child",$this->_cer);
            $em->setExpected($epd,"child",$this->_epdCer);
            
            $this->_page->setupComponents();
            $form = $this->_page->getComponent('Form');
            $this->_field = $form->getField(getenv('ADD_FIELD_TEST'));
            ADD::log()->info("field:".print_r($this->_field,1));
	}	

	function testCalculationWithEmptyValues() {
		$page = $this->_page;
		//$map = array(array("assessment_date","20/01/2013"),array("height",180));
		//$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter '% predicted FEV<sub>1</sub>','ISWT','MRC dyspnoea scale' in 'Exercise' page to calculate the iBODE score",
			"Please enter 'BMI' in 'E/P disease' page to calculate the iBODE score"
		));
	}
        
        function testCalculationWithMissingValues() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",68),array("iswt",20));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
		$this->_field->afterPopulateFields($page);
		$this->assertHasWarnings(array(
			"Please enter 'MRC dyspnoea scale' in 'Exercise' page to calculate the iBODE score",
			"Please enter 'BMI' in 'E/P disease' page to calculate the iBODE score"
		));
	}
        
        function testCalculationWithMissingValues2() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",68),array("iswt",20));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",34));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array(
			"Please enter 'MRC dyspnoea scale' in 'Exercise' page to calculate the iBODE score",
		));
	}
        
        function testIBodeZero() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",68),array("iswt",260),array("mrc_grade",2));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",34));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasNoWarnings();
                $this->assertEquals(0,$this->_field->getValue());
	}
        
        function testIBodeOne() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",55),array("iswt",200),array("mrc_grade",3));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",19));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasNoWarnings();
                $this->assertEquals(1,$this->_field->getValue());
	}
        
        function testIBodeTwo() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",37),array("iswt",120),array("mrc_grade",4));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",27));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasNoWarnings();
                $this->assertEquals(2,$this->_field->getValue());
	}
        
        function testIBodeThree() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",34),array("iswt",76),array("mrc_grade",5));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",27));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasNoWarnings();
                $this->assertEquals(3,$this->_field->getValue());
	}
        
        function testIBodeOutOfRange() {
		$page = $this->_page;
		$map = array(array("pc_pred_fev1",80),array("iswt",76),array("mrc_grade",5));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",27));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		//$this->assertEquals(array($this->_page->getSetting('PredictedQmvc.field')=>""),$output);
		$this->assertHasWarnings(array("Patient cannot be assigned to an iBODE index. Their measurements are not within the required ranges."));
                $this->assertEquals("N/A",$this->_field->getValue());
	}
        
        function testIBodeChangeWarning() {
		$page = $this->_page;
                $this->_field->setValue("N/A");
		$map = array(array("pc_pred_fev1",37),array("iswt",120),array("mrc_grade",4));
		$this->_cer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
                
                $map2 = array(array("bmi",27));
		$this->_epdCer->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map2));
		
                $this->_field->afterPopulateFields($page);
		$this->assertHasMessages(array("iBODE score has changed from 'N/A' to '2'"));
                $this->assertEquals(2,$this->_field->getValue());
	}
       
}
?>