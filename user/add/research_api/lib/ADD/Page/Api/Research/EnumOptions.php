<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of Patient
 *
 * @author rcf8
 */

class ADD_Page_Api_Research_EnumOptions extends ADD_Page_Api_Research {
	function apiOutput() {
    	$add = ADD::sys();
		
        $encType = $add->assertParam('encounter');
		$fieldName = $add->assertParam('field');
		$encounterLookup = new ADD_Query_EncounterLookup(ADD::sys()->getModules());
		$page = $encounterLookup->lookupPage($encType);
		if(!$page) {
			throw new Exception("Page with type '$encType' not found");
		}
		$form = $page->getComponent('Form');
		$form->setup();
		$field = $form->getField($fieldName);
		$option  = $field->getOption();
		return array("data"=>json_encode($option));
	}
	function getExpectedPermissions() {
		return array('get');
	}
}
