<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StudyOption
 *
 * @author rcf8
 */
class ADD_Component_StudyOption extends ADD_Component {
	function getStudyDetails() {
		$sdetailsPage = $this->_page->getSiblingPage('sdetails');
		$sdetails = $sdetailsPage->getEncounterRecord();
		$sdetails->retrieve();
		return array($sdetailsPage,$sdetails);
	}
	
}
