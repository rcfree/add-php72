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

class ADD_Page_Api_Research_ConceptStructure extends ADD_Page_Api_Research {
	function apiOutput() {
		$add = ADD::sys();
		$pim = $add->assertParam('pim');
		$page = $add->getPageFromPim($pim);
		if(!$page) {
			throw new Exception("Page with pim '$pim' not found");
		}
		$cdClassName = "ADD_ConceptExtractor_".$page->getSetting('concept_extractor',"Form")."ConceptExtractor";
		$conceptData = new $cdClassName($page);
    	
		
		$concepts = $conceptData->createConcepts();
    	return array("data"=>$concepts);
	}
	
	function getExpectedPermissions() {
		return array('get');
	}
}
