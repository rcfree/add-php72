<?php

class ADD_Component_DomainMenuContent extends ADD_Component_Modal {
	private $_error = false;
	function contentType(&$contentType) {
		$contentType="Html";
	}
	
	function menuPath(&$menuPath) {
		$menuPath="client/menu-content";
	}
	
	function generate() {
		$add = ADD::sys();
		
		$pim = $add->getParam('pim');
		$mod = $add->getPageFromPim($pim)->getModule();
		
		$currentPim = $add->getParam('current');
		$currentPage = $add->getPageFromPim($currentPim);
		$add->getAccessManager()->setCurrentPage($currentPage);
		ADD::sys()->setState('openMenu', $currentPage->getModule()->getSuperModule()->getName());
		$menus = array();
		ADD::sys()->getPatientId($add->getParam('pid'));
		foreach ($mod->getTopPages() as $page) {
			$menuHtml = null;
			try {
				$menuHtml = $page->createMenu();
			}
			catch(Exception $ex) {
				if(!$menuHtml) $menuHtml = ADD::web()->newContent();
				$menuHtml->addTextElement("p","Unexpected menu error:".\ADD::web()->errorMessage($ex));
			}
			if ($menuHtml && $menuHtml->output()) $menus[]=$menuHtml;
		}
		$mainContent = ADD::web()->getContent('main');
		$mainContent->addHtml($menus);
		$add->setState('no_modal',true);
		ADD::web()->addJavascript("$('.collapse').on('show',function() { 
				$(this).parent('.accordion-group').find('h5 span.right-caret').attr('class','down-caret');
			});
			$('.collapse').on('hide',function() { 
				$(this).parent('.accordion-group').find('h5 span.down-caret').attr('class','right-caret');
			});");
	}
	
	function _addError(Exception $ex) {
		ADD::web()->addError($ex->getMessage());
		if(ADD::sys()->config('stacktrace')) {
			ADD::web()->addError("<pre>".$ex->getTraceAsString()."</pre>");
		}
		$this->_error = true;
	}
}
