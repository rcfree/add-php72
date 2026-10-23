<?php

class ADD_Component_LookupDictGroupItems_AjaxTest extends ADD_Test_AjaxBase {
    public $_epdCer = null;
    function getAjaxFunctionToTest() {
		return "LookupDictGroupItems";
	}
	function getAjaxSourcePage() {
		return $this->_page->getName();
	}
	function getAjaxSourceModule() {
		return $this->_module->getName();
	}
	
	/**
	 * @test
	 */
	function it_should_return_inputs_containing_the_field_defined_in_the_page_settings() {
		$comp = $this->_page->getComponent('LookupDictGroupItems');
		$page = $this->_page;
		
		$inputs = $comp->ajaxInputs($this->_page);
		$this->assertEquals(array($page->getSetting('LookupDictGroupItems.lookup_field')),$inputs);
	}
	
	/**
	 * @test
	 */
	function it_should_return_no_options_if_no_group_field_is_supplied() { 
		$page = $this->_page;
		$rvmap = array(array("id"=>"1","value"=>"A1","title"=>"Aardvark"),array("id"=>"2","value"=>"B1","title"=>"Bunny"));
        ADD::db('dictionary')->expects($this->never())->method("getDictionaryItemsInGroup");
		$values = array();
		$expected = array("outputs"=>array(
			$page->getSetting('LookupDictGroupItems.output_field')=>array(
				"options"=>array(),
			)
			),
			"messages"=>array(),
			"fields"=>array($page->getSetting('LookupDictGroupItems.lookup_field'))
		);
		$this->assertAjaxOutputEquals($values,$expected);
    }
	
	/**
	 * @test
	 */
	function it_should_return_empty_no_options_if_group_field_value_is_null_or_empty() { 
		$page = $this->_page;
		$rvmap = array(array("id"=>"1","value"=>"A1","title"=>"Aardvark"),array("id"=>"2","value"=>"B1","title"=>"Bunny"));
        ADD::db('dictionary')->expects($this->never())->method("getDictionaryItemsInGroup");
		$values = array($page->getSetting('LookupDictGroupItems.lookup_field')=>"");
		$expected = array("outputs"=>array(
			$page->getSetting('LookupDictGroupItems.output_field')=>array(
				"options"=>array(),
			)
			),
			"messages"=>array(),
			"fields"=>array($page->getSetting('LookupDictGroupItems.lookup_field'))
		);
		$this->assertAjaxOutputEquals($values,$expected);
    }
	
	/**
	 * @test
	 */
	function it_should_return_options_in_group_if_group_field_value_supplied() { 
		$page = $this->_page;
		$rvmap = array(array("id"=>"1","value"=>"A1","title"=>"Aardvark"),array("id"=>"2","value"=>"B1","title"=>"Bunny"));
        ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemsInGroup")->with($page->getSetting('LookupDictGroupItems.dictionary'),1)->will($this->returnValue($rvmap));
		$values = array($page->getSetting('LookupDictGroupItems.lookup_field')=>"1");
		$expected = array("outputs"=>array(
			$page->getSetting('LookupDictGroupItems.output_field')=>array(
				"options"=>array(
					array("value"=>"","label"=>$page->getSetting('LookupDictGroupItems.empty_option')),
					array("value"=>"A1","label"=>"Aardvark"),
					array("value"=>"B1","label"=>"Bunny"),
				),
			)
			),
			"messages"=>array(),
			"fields"=>array($page->getSetting('LookupDictGroupItems.lookup_field'))
		);
		$this->assertAjaxOutputEquals($values,$expected);
    }

}

?>
