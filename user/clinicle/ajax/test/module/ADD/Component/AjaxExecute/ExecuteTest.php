<?php

require_once(getenv('ADD_PATH')."/core/module/ajax/lib/ADD/Component/AjaxInterface.php");
class ADD_Component_AjaxExecute_ExecuteTest extends ADD_Test_EncounterBase {
	function setUp() {
		$this->ajax = new ADD_Component_AjaxImpl();
		parent::setUp();
		ADD::web()->setContentClass('Json');
		$this->_page->getComponents()->add('AjaxImpl');
	}
	
	/**
	 * @test
	 */
	function data_should_be_returned_when_output_is_called() {
		$ae = $this->_page->getComponent('AjaxExecute');
		ADD::sys()->setParams(array('f'=>'AjaxImpl','p'=>'ajax','m'=>'ajax'));
		print_r(ADD::web()->getContent()->getJsonData());
	}
}

class ADD_Component_AjaxImpl extends ADD_Component implements ADD_Component_AjaxInterface {
	public function ajaxFunction($values, ADD_Page $callPage) {
		
	}

	public function ajaxInputs(ADD_Page $callPage) {
		
	}

}