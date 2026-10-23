<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_ConceptExtractor_FormConceptExtractorTest extends PHPUnit_Framework_TestCase {
	private $page;
	function setUp() {
		$env = new \ClinicLE\System\Environment\StandardEnvironment("/Users/rcf8/Projects/add","local-stable");
		$init = new \ClinicLE\System\Initialiser($env);
		
		$sys = ADD::init($init);
		$sys->initServiceContainer();
		
		$this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
		$this->form = new ADD_Component_Form($this->page);
		$this->page->expects($this->exactly(2))->method("getComponent")->with("Form")->will($this->returnValue($this->form));
		
		$formSetup = new ADD_Form_FileSetup($this->form->getFieldContent());
		$formSetup->loadAndInit(__DIR__."/../data/test-form.ftx", "RowData",$this->page);	
		$this->SUT = new ADD_ConceptExtractor_FormConceptExtractor($this->page);
	}
	
	/**
	 * @test
	 */
	function it_should_output_expected_concepts() {
		$expected = file_get_contents(__DIR__."/../data/test-concepts.json");
		$conceptData = $this->SUT->createConcepts($this->page);
		$this->assertEquals($expected,json_encode($conceptData));
	}
}