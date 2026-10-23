<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Component_Letter extends ADD_Component {
	function contentType(&$contentType) {
		$contentType="Rtf";
	}
	function menuPath(&$menuPath) {
		$menuPath="letter";
	}
	
	function getComponentList() {
		$cl = array(
			'MenuItem'
		);
		return $cl;
	}
	public function menuLabel(&$menuLabel) {
        $page = $this->_page;

        if (!$page->getPermission('view')) return;
        
        $menuLabel = $page->getSetting('title');
	}
	public function menuIcon(&$menuIcon) {
		$menuIcon="icon-print";
	}
	
	public function expectedPermissions(&$expPerms) {
		$expPerms=array("view");
	}

	function output() {
        ADD::web()->getContent('title',"Html");
		
		$content = ADD::web()->getContent();
		$page = ADD::sys()->getPageInModule("pr","general");
		$rd = new ADD_Tool_ReportData(79,"pr",3322,$page,array("[","]"));
		$rtf = new ADD_Tool_RTF(ADD::sys()->getModuleWorkPath("letter")."/test/data/example.rtf");
		$rtf->fillFieldsUsingData($rd);
		
		$adb = ADD::db('audit');
		
        $content->clearBuffer();
		$content->setRtf($rtf);
		
		return $content->output();
	}
    
}