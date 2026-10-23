<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Page_Api_Encounter extends ADD_Page_Api {
	function apiOutput() {
		$add = ADD::sys();
		$log = ADD::log($this);
		$this->_pageInModuleName = $add->getParam('pim');
		$this->_patientId = $add->getParam('patientId');
		$this->_parentId = $add->getParam('parentId');
		$this->_encounterId = $add->getParam('encounter_id');
		$action = $add->getParam('action','view');
		$funcName = "_".$action."Encounter";
		return call_user_func(array($this,$funcName));
	}
	
	function _deleteEncounter() {
		$em = ADD::sys()->getEncounterManager();
		$er = $em->recordById($this->_encounterId);
                $page = ADD::sys()->getPageFromPim($this->_pageInModuleName);
                $er->setType($page->getSetting("encounter"));
                $er->retrieve();
		$er->delete();
                ADD::log($this)->info("deleteEncounter:".print_r($er,1));
		
		ADD::web()->addMessage("Successfully deleted record");
		return array("success"=>1);
	}
	
}