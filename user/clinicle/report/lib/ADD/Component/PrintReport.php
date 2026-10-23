<?php

class ADD_Component_PrintReport extends ADD_Component {
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
	public function menuPath(&$menuPath) {
		$menuPath = "report";
	}
    public function pathNotPage(&$pathNotPage) {
            $pathNotPage=true;
    }
	public function menuParams(&$menuParams) {
		$add = ADD::sys();
        $page = $this->_page;

        $ppage = $page->getParentPage();
		if(!$ppage) throw new ADD_PageNotFoundException("Parent of '".$page->getName()."' not found");
		$per = $ppage->getEncounterRecord();
		
		if ($per->exists()) {
			$reportName = $page->getSetting('report-name');
			if (!$reportName) throw new ADD_PageException("No 'report-name' setting provided for 'PrintReport' page type");
			
			$menuParams = array(
				"pid"=>$add->getPatientId(),
				"name"=>$reportName,
				"id"=>$per->getId(),
				"module"=>$page->getModule()->getAlias(),
				"type"=>is_a($per,"\ClinicLE\Data\Record\SectionRecord") ? "section" : "encounter"
			);
		}
	}
	public function menuAttrs(&$menuAttrs) {
		$menuAttrs['target']='_blank';
	}
}
?>