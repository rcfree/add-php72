<?php


class ADD_Component_Content_OverviewEntry extends ADD_Component {
	protected $_componentList = array('PatientCheck','Breadcrumb','Encounter_Single', 'Form_DB','AccessControl','OptionContainer','SaveButton','MenuItem_Single','Locking','RequiredFields_Notify','OverviewUpdate','ConfirmSave');
	
	function title(&$title) {
        $page = $this->_page;
		$ppage = $page->getParentPage();
		$title = $page->getSetting('title') . " (".$ppage->getSetting('title').")";
	}
	
	function expectedPermissions(&$expPerms) {
		if($this->getSetting('viewonly')) {
			$expPerms[]="view";
			return;
		}
		$expPerms = $expPerms + array('create','edit','view');
	}
}
?>