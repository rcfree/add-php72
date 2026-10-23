<?php
class ADD_Component_Content_EncounterEntry extends ADD_Component {
	
	function getComponentList() {
	    $fileForm = $this->getSetting("file_form");
		return array(
			'PatientCheck',
			'Breadcrumb',
			'Encounter_Single', 
			'Autosave',
			$fileForm ? 'Form_File' : 'Form_DB',
			'AccessControl',
			'OptionContainer',
			'RecalculateButton',
			'SaveButton',
			'MenuItem_Single',
			'RequiredFields_'.($this->_page->getSetting('RequiredFields.validate') ? 'Validate' : 'Notify'),
			'Locking',
			'ConfirmSave',
			/*'Verification'*/           
		);
	}

	
	function title(&$title) {
        $page = $this->_page;
		$ppage = $page->getParentPage();
		$title = $page->getSetting('title') . " (".$ppage->getSetting('title').")";
	}
	
	function expectedPermissions(&$expPerms)
    {
        $page = $this->_page;
        $expPerms = $expPerms + array('create', 'edit', 'view');
    }
}
?>