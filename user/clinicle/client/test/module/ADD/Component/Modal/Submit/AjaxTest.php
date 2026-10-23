<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


 class ADD_Component_Modal_Submit_AjaxTest extends ADD_Test_EncounterBase {
	
	public function setUp() {
		parent::setUp();
		$this->testPage = $this->getMockBuilder('ADD_Page')->disableOriginalConstructor()->getMock();
		$form = new ADD_Component_Form($this->testPage);
		$enc = $this->getMock("ADD_Component_Encounter_Single",null,array($this->testPage));
		$field = new ADD_Field_Text(array("Field"=>"test"));
		$form->addField($field);
		$compCol = new ADD_ComponentCollection($this->testPage);
		$compCol->add($form);
		$compCol->add($enc);
		$this->testPage->expects($this->any())->method("getComponents")->will($this->returnValue($compCol));
		$this->testPage->expects($this->once())->method("submitForm");
		$this->testPage->expects($this->any())->method("html")->will($this->returnValue(ADD::web()->getContent()));
		$this->testMod = $this->getMockBuilder('ADD_Module')->disableOriginalConstructor()->getMock();
		$this->testMod->expects($this->any())->method("getName")->will($this->returnValue("testmod"));
		$this->testMod->expects($this->any())->method("getPageInStructure")->will($this->returnValue($this->testPage));
		ADD::sys()->getModules()->add($this->testMod);	
		$em = ADD::sys()->getEncounterManager();
		$er = $this->getMock("ADD_EncounterRecord");
		$em->setExpected($this->testPage,"child",$er);
		
	}
	
	public function getMockClassType() {
		return "Json";
	}
	
	/**
	 * @test
	 */
	function it_should_submit_to_the_supplied_form() {
		$values = array(ADD_Component_Modal::MODULE_POST=>"testmod",ADD_Component_Modal::PAGE_POST=>"testpage","test"=>"testvalue",ADD_Component::PATIENT_ID=>123);
		$this->_page->createOutput($values);
		$obsOutput = preg_replace('/\<input type="hidden" value=".+" name="__token"\/\>/',"",ADD::web()->getContent()->output());
		$expOutput = '<div style="" class="field field-element content-test"><label class="control-label"></label><input type="text" name="test" value="" maxlength="" style="width:16em" id="edit-test"/></div><input type="hidden" value="testmod" name="__module"/><input type="hidden" value="testpage" name="__page"/>';
		print_r(ADD::web()->getContent());
	}
 }