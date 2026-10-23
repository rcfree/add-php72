<?php

class ADD_Component_Content_SummaryPage extends ADD_Component {
	function getComponentList() {
		$cl = array(
			'PatientCheck',
			'Breadcrumb',
			'Form_File',
			'AccessControl',
			'MenuItem_Single',
		);
		return $cl;
	}
	
	function title(&$title) {
        $page = $this->_page;
		$ppage = $page->getParentPage();
		$title = $page->getSetting('title') . " (".$ppage->getSetting('title').")";
	}
	
	function expectedPermissions(&$expPerms) {
        	$page = $this->_page;
		$expPerms = array('view');
	}
      
	function menuIcon(&$menuIcon) {
		$menuIcon=$this->getSetting('icon',$this->_page->getSetting("icon","icon-print"));
	}
}
?>
