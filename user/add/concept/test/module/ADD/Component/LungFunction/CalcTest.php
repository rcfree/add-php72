<?php
class Component_LungFunction_CalcTest extends PHPUnit_Framework_TestCase {
	function setUp() {
		new ADD_System(getenv('ADD_PATH'),getenv('ADD_PROFILE'));
	}
	function testMaleCalculation() {
		$values = ADD_Component_LungFunction::calcPredictedValues("pred", "M","180","90",false);
		$this->assertEquals(array("pred_fev1"=>2.64,"pred_fvc"=>3.688,"pred_fev1_fvc"=>71.01,"pred_pef"=>439.92),$values);
	}
	
	function testFemaleCalculation() {
		$values = ADD_Component_LungFunction::calcPredictedValues("pred", "F","180","90",false);
		$this->assertEquals(array("pred_fev1"=>2.26,"pred_fvc"=>2.744,"pred_fev1_fvc"=>72.0,"pred_pef"=>365.4),$values);
	}
	
	function testCorrectionFactorInCalculation() {
		$values = ADD_Component_LungFunction::calcPredictedValues("pred", "M","180","90",true);
		$this->assertEquals(array("pred_fev1"=>2.376,"pred_fvc"=>3.3192,"pred_fev1_fvc"=>71.01,"pred_pef"=>395.928),$values);
	}
	
	function testOtherCalculation() {
		$values = ADD_Component_LungFunction::calcPredictedValues("pred", "M","140","70",true);
		$this->assertEquals(array("pred_fev1"=>1.35,"pred_fvc"=>1.7136,"pred_fev1_fvc"=>74.61,"pred_pef"=>309.744),$values);
	}
}
?>