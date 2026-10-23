<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AjaxBase
 *
 * @author rcf8
 */
class ADD_Test_AjaxBase extends ADD_Test_EncounterBase {
	
	public function setUp() {
		parent::setUp();
		ADD::web()->setContentClass('Json');
		$this->ajaxExecute=ADD::sys()->getPageInModule('ajax','ajax')->getComponent('AjaxExecute');
	}
	function getAjaxFunctionToTest() {
		throw new Exception("getAjaxFunctionToTest method missing");
	}
	function getAjaxSourcePage() {
		throw new Exception("getAjaxSourcePage method missing");
	}
	function getAjaxSourceModule() {
		throw new Exception("getAjaxSourceModule method missing");
	}
	function assertAjaxOutputEquals($values,$expected) {
		$values = $values + array('f'=>$this->getAjaxFunctionToTest(),"m"=>$this->getAjaxSourceModule(),"p"=>$this->getAjaxSourcePage());
		ADD::sys()->setParams($values);
		
		$content = $this->ajaxExecute->createOutput();
		
		$this->assertEquals(json_encode($expected),$content->output());
	}
}

