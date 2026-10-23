<?php

class ADD_Component_FieldContent extends ADD_Component_Modal {
	private $_error = false;
	function contentType(&$contentType) {
		$contentType="Html";
	}
	
	function menuPath(&$menuPath) {
		$menuPath="client/field-content";
	}
	
	function generate() {
		$add = ADD::sys();
		
		$currentPim = $add->getParam('pim');
		$field = $add->getParam('field');
		$currentPage = $add->getPageFromPim($currentPim);
		$currentPage->setupComponents();
		$currentForm = $currentPage->getComponent('Form');
		$currentField = $currentForm->getField($field);
		$currentPage->createContent();
		ADD::web()->clear();
		$currentField->createMarkup($currentPage);
	}
	
	function _addError(Exception $ex) {
		ADD::web()->addError($ex->getMessage());
		if(ADD::sys()->config('stacktrace')) {
			ADD::web()->addError("<pre>".$ex->getTraceAsString()."</pre>");
		}
		$this->_error = true;
	}
	
	function viewFile(&$viewFile) {
		$viewFile="module/clinicle/client/field";
	}
}
