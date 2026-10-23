<?php


class ADD_Component_UserLogging extends ADD_Component {
	function userLogin(ADD_AccessManager $accessManager) {
		$ul = ADD_UserLogin::instanceFromAccessManager($accessManager);
		
		$ul->login();
	}
	function baseContent() {
		$add = ADD::sys();
		$userLogin = ADD_UserLogin::instanceFromAccessManager(ADD::sys()->getAccessManager());
		if(!$userLogin->hasSessionId()) {
			return;
		}
		$userLogin->unlock();
		$page = ADD::sys()->getAccessManager()->getCurrentPage();
			
		if(!$page) return;
		if(!$page->hasComponent('Content')) return;
		$contentComp = $page->getComponent('Content');
		if($contentComp->getType()=="Content_SummaryPage") return;
		if($contentComp->getType()=="Content_PublicEntry") return;
		
		$page->getComponents()->add("RecordInUse");
		$page->getComponents()->get("RecordInUse")->setUserLogin($userLogin);	
	}
	
	function userLogout(ADD_AccessManager $accessManager) {
		$ul = ADD_UserLogin::instanceFromAccessManager($accessManager);
		$ul->logout();
	}
}