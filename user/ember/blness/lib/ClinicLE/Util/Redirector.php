<?php
namespace Clinicle\Util;
use ADD;
use ADD_Util;

class Redirector {
	function redirectUsingSetting($page,$pid,$eid, $description) {
		$redirect = $page->getSetting('redirect');
		if(!$redirect) {
			return;
		}
		$pageRedirect = $page->getModule()->getPage($redirect);
		if(!$pageRedirect->getPermission("create")) {
			ADD::web()->addWarning($description." created but no permission to create ".$pageRedirect->getSetting('title'));
			return;
		}
		$menuPath = $pageRedirect->createMenuPath();
		$add = ADD::sys();
		$menuParams = array('pid'=>$pid,'eid'=>$eid);
		$menuUri = ADD_Util::uri($menuPath,$menuParams);
		ADD::web()->addMessage("Successfully created ".$description);
			
		$add->setState("redirect",$menuUri);
	}
}