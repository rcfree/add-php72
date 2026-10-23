<?php

class ADD_Component_DownloadFile extends ADD_Component {
	private $_contentClass;
	public function menuPath(&$menuPath) {
		$mod = $this->_page->getModule();
		$menuPath = $mod->getSuperModule()->getAlias()."/".$this->_page->getName();
	}

    function contentType(&$contentType) {
        $contentType=$this->getPage()->getSetting("content_type","FileContent");
    }
	function output() {
		if (!$this->isAvailable()) throw new ADD_NoPermissionToAccessException("You do not have permission to view this file");
		
		$file = basename(ADD::sys()->getParam('file'));
		$filePath = ADD::sys()->getPath()."/".$this->_page->getSetting('file_path');
		
		$content = ADD::web()->getContent();
		if(ADD::sys()->getParam('download')) {
			$content->setDownload(true);
		}
		$fileName = urldecode($file);
		if($this->_page->getSetting('use_subdirs')) {
			$filePath .= "/".substr($fileName,0,1)."/".substr($fileName,1,1);
		}
		
		$decodedFile = $filePath."/".urldecode($file);
		$content->setFileName($decodedFile);
		$actionInfo = ADD_Util::serialize(array("full_filename"=>$decodedFile));
		ADD::db('audit')->createAudit(array("record_type"=>"file","record_name"=>$file,"action"=>"view","action_info"=>$actionInfo));
	}
	
	function isAvailable() {
		return $this->_page->getPermission('view');
	}
	
	function expectedPermissions(&$expPerms) {
		$expPerms=array("view");
	}
}
?>