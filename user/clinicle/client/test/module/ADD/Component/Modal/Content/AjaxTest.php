<?php

class ADD_Component_Modal_Content_AjaxTest extends ADD_Test_EncounterBase {
	
	public function setUp() {
		parent::setUp();
		$this->testPage = $this->getMockBuilder('ADD_Page')->disableOriginalConstructor()->getMock();
		$form = new ADD_Component_Form($this->testPage);
		$field = new ADD_Field_Text(array("Field"=>"test"));
		$form->addField($field);
		$compCol = new ADD_ComponentCollection($this->testPage);
		$compCol->add($form);
		$this->testPage->expects($this->any())->method("getComponents")->will($this->returnValue($compCol));
		$this->testPage->expects($this->any())->method("html")->will($this->returnValue(ADD::web()->getContent()));
		$this->testMod = $this->getMockBuilder('ADD_Module')->disableOriginalConstructor()->getMock();
		$this->testMod->expects($this->any())->method("getName")->will($this->returnValue("testmod"));
		$this->testMod->expects($this->any())->method("getPageInStructure")->will($this->returnValue($this->testPage));
		ADD::sys()->getModules()->add($this->testMod);
	}
	/**
	 * @test
	 */
	function it_should_output_the_form_contents() {
		ADD::sys()->setParam('pim','testmod:testpage');
		
		$this->_page->createContent();
		$obsOutput = preg_replace('/\<input type="hidden" value=".+" name="__token"\/\>/',"",ADD::web()->getContent()->output());
		$expOutput = '<div class="field field-element content-test"><label class="control-label"></label><input type="text" name="test" value="" maxlength="" style="width:16em" id="edit-test"/></div><input type="hidden" value="testmod:testpage" name="__pim"/><input type="hidden" value="" name="__parent"/><input type="hidden" value="" name="__pid"/>';
		$this->assertEquals($expOutput,$obsOutput);
	}
	
}