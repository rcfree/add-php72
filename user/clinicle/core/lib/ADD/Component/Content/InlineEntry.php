<?php

class ADD_Component_Content_InlineEntry extends ADD_Component {
	protected $_componentList = array('PatientCheck', 'Breadcrumb', 'Form_DB','OptionContainer','MenuItem_Multiple','AccessControl','ConfirmSave','Encounter_Single_Inline');
	
	function title(&$title) {
        $page = $this->_page;
		$title = $page->getSetting('title');
	}
	
	public function beforePopulate() {
		$add = ADD::sys();
		$add->setState('new',true);
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms = $expPerms + array('create','edit','view','delete');
    }
}
?>
