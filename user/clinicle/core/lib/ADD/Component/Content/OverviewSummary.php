<?php

class ADD_Component_Content_OverviewSummary extends ADD_Component {
	
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Breadcrumb',
			'Encounter_Single', 
			'Form_DB',
			'MenuItem_Single',
			'OverviewUpdate',
			'RequiredFields_Notify'
		);
		return $cl;
	}
	function title(&$title) {
        $page = $this->_page;
		$title = $page->getSetting('title');
	}
	public function menuLabel(&$menuLabel) {
        $page = $this->_page;
        if (!$page->getPermission('view')) return;
        
        $menuLabel = $page->getSetting('title');
	}
	function expectedPermissions(&$expPerms) {
		$expPerms+=array('view');
	}
	
	function afterSubmit(ADD_Page $submitPage) {
		$page = $this->_page;
		
	}
}
?>