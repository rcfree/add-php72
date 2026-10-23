<?php

class ADD_Component_ViewReport extends ADD_Component {
	private $_contentClass;
	public function menuPath(&$menuPath) {
		$menuPath = "report/view";
	}
	function setup() {
		$file = ADD::sys()->getParam('file');
	}
	function contentType(&$contentType) {
		$contentType="FileView";
	}
	function output() {
		if (!$this->isAvailable()) throw new ADD_NoPermissionToAccessException("You do not have permission to view this report");
		
		$file = ADD::sys()->getParam('file');
		$reportPath = ADD::sys()->config('report_path');
		$content = ADD::web()->getContent();
		$content->setFileName($reportPath."/".urldecode($file));
	}
	
	function isAvailable() {
		$file = ADD::sys()->getParam('file');
		$matches = array();
		$pathinfo = pathinfo($file);
		
		$filename = $pathinfo['filename'];
		
		$filebits = explode("_",$filename);
		
		$modName = $filebits[0];
		$reportName = $filebits[1];
		
		$reportModule = ADD::sys()->getModule($modName);
		$reportPage = $reportModule->getPage($reportName);
		if (!$reportPage->getPermission('view')) {
			return false;
		}
		return true;
	}
}
?>