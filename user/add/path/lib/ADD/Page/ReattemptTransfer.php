<?php

class ADD_Page_ReattemptTransfer extends ADD_Page { 
    function isWebContent() {
    	return true;
	}
	function createMenuPath() {
        return "path/reattempt";
	}
        
	function createContent() {
		$modPages = $this->getModule()->getPages();
		$anyPerms = false;
		foreach ($modPages as $modPage) {
			if(!$modPage->hasAnyPermissions(array('create','edit','view'))) {
				continue;
			}
			$anyPerms=true;
			break;
		}
		if(!$anyPerms) {
			throw new ADD_NoPermissionToAccessException("Sorry you have no permission to access pathology data");
		}
		$patientId = ADD::sys()->getPatientId();
		if(!$patientId) return;
		
		$triggerFile = ADD::sys()->getPath()."/tmp/.path-patient-".$patientId;
		if(file_exists($triggerFile)) unlink($triggerFile);
		ADD::sys()->setState('redirect','/?pid='.ADD::sys()->getParam('pid')."&forcepath=1");
	}
}
