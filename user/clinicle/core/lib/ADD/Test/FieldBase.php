<?php

$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Test_FieldBase extends ADD_Test_EncounterBase {
    function setUp() {
		parent::setUp();
        $this->_page->setupComponents();
        $form = $this->_page->getComponent('Form');
		$form->setup();
		$form->beforeInstall();
		$this->_field = $form->getField(getenv('ADD_FIELD_TEST'));
		if(!$this->_field) {
			throw new Exception("Field '".getenv('ADD_FIELD_TEST')."' was not found in ".$this->_page->getName()." page of module ".$this->_page->getModule()->getName());
			
		}
        $this->loadTestConfig();
    }
}
