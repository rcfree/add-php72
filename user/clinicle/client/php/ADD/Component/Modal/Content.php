<?php

class ADD_Component_Modal_Content extends ADD_Component_Modal {
	private $_pim;
	private $_error = false;
	function contentType(&$contentType) {
		$contentType="Html";
	}
	
	function menuPath(&$menuPath) {
		$menuPath="client/modal-content";
	}
	function displayAccess() {
		$add = ADD::sys();
		try {
			if($add->getParam('readonly')==1) {
				$add->setState('readOnly',true);
			}
			$pageInModuleName=$add->assertParam('pim');
			$this->_pim=$pageInModuleName;
			$temp = explode(":",$pageInModuleName);
			$pageInModule = $add->getPageInModule($temp[0],$temp[1]);
			$this->updateParentId($pageInModule);
			
			$add->getAccessManager()->setCurrentPage($pageInModule);
		
			if(!$pageInModule) throw new ADD_PageNotFoundException("Page in module '".$this->_pim."' not found");
			$this->_formPage = $pageInModule;
			$pageInModule->setupComponents();
			if($add->getParam('a')=="create") {
				$add->setState('new',true);
			}

			$this->_formPage->getComponents()->callFunction('displayAccess');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	function updateParentId($page) {
		$add = ADD::sys();
                //$log = ADD::log($this);
		if($add->getParam(ADD_Component_Modal::PARENT_GET)) {
                    //$log->info("Parent get:".$add->getParam(ADD_Component_Modal::PARENT_GET));
			return;
		}
		$ppage = $page->getParentPage();
		if(!$ppage || !$ppage->hasComponent('Encounter')) {
                    //$log->info("No parent page");
			return;
		}
		$penc = $ppage->getComponent('Encounter');
		if(is_a($penc,"ADD_Component_Encounter_Section")) {
			$per = $penc->getEncounterRecord();
			$add->setParam(ADD_Component_Modal::PARENT_GET,$per->getId());
			return;
		}
		if(!is_a($penc,"ADD_Component_Encounter_RepeatSection")) {
			return;
		}
		
		if($add->getParam('a')=="create") {
			return;
		}
		$em = $add->getEncounterManager();
                //$log->info("Use record ID".$add->getParam('id'));
		$er = $em->recordById($add->getParam('id'));
		$er->retrieve();
		$parentId = $er->getParent()->getId();
		$add->setParam(ADD_Component_Modal::PARENT_GET,$parentId);
	}
	function beforeGenerate() {
		if ($this->_error) return;
		try {
			$this->_formPage->getComponents()->callFunction('beforeGenerate');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	function generate() {
		if ($this->_error) return;
		try {
			$this->_formPage->getComponents()->callFunction('generate');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	function afterGenerate() {
		if ($this->_error) return;
		try {
			$this->_formPage->getComponents()->callFunction('afterGenerate');
			$html = ADD::web()->getContent();
			$html->addElement("input",array("type"=>"hidden","value"=>$this->_pim,"name"=>"__pim"));
			$html->addElement("input",array("type"=>"hidden","value"=>ADD::sys()->getParam(ADD_Component_Modal::PARENT_GET),"name"=>ADD_Component_Modal::PARENT_POST));
			$html->addElement("input",array("type"=>"hidden","value"=>ADD::sys()->getPatientId(),"name"=>ADD_Component::PATIENT_ID));
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
		
	}
	function beforePopulate() {
		if ($this->_error) return;
		try {
			$this->_formPage->getComponents()->callFunction('beforePopulate');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	
	function populate() {
		
		if ($this->_error) return;
		try {
			
			$this->_formPage->getComponents()->callFunction('populate');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	function afterPopulate() {
		if ($this->_error) return;
		try {
			$this->_formPage->getComponents()->callFunction('afterPopulate');
		}
		catch(Exception $ex) {
			$this->_addError($ex);
		}
	}
	
	function _addError(Exception $ex) {
		ADD::web()->addError($ex->getMessage());
		if(ADD::sys()->config('stacktrace')) {
			ADD::web()->addError("<pre>".$ex->getTraceAsString()."</pre>");
		}
		$this->_error = true;
	}
}
