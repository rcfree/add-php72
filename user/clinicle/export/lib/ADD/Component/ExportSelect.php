<?php

class ADD_Component_ExportSelect extends ADD_Component {
	
	public function title(&$title) {
		$title = $this->_page->getTitle();
	}
	public function displayAccess() {
		if(!$this->_page->getPermission('select')) throw new ADD_NoPermissionToAccessException("You do not have access to the export select page");
	}
	public function submitAccess($values) {
		$this->displayAccess();
	}
	
	
	public function expectedPermissions(&$expPerms) {
		$expPerms=array("select");
	}
	
}
