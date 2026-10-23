<?php

class ADD_Component_DeleteEncounterRow extends ADD_Component {
	function contentType(&$contentType) {
		$contentType="Json";
	}
	
	function menuPath(&$menuPath) {
		$menuPath="client/delete";
	}
	function displayAccess() {
		$this->_modalModule = ADD::sys()->getParam(ADD_Component_Modal::MODULE_GET);
		$this->_modalPage = ADD::sys()->getParam(ADD_Component_Modal::PAGE_GET);
		if(!$this->_modalPage->getPermission('delete')) throw new ADD_NoPermissionToAccessException("You do not have permission to delete '".$this->_modalPage->getSetting('title')."' records");
	}
	function output() {
		$em = ADD::sys()->getEncounterManager();
		$er = $em->recordById(ADD::sys()->getParam('id'));
		$er->retrieve();
		$er->delete();
		$content = ADD::web()->getContent();
		$content->setJsonData(array("success"=>true));
	}
}
