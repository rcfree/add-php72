<?php


class ADD_Component_Breadcrumb extends ADD_Component {
	
	function breadcrumb(&$breadcrumb) {
        $page = $this->_page;
        if($page->getSetting('no_breadcrumb')) return;
		array_unshift($breadcrumb,$page->getSetting('title'));
		$parentPage = $page->getParentPage();
		if(!$parentPage) return;
		array_unshift($breadcrumb,$this->_getTitleFromPage($parentPage));
		$gpPage = $parentPage->getParentPage();
		if(!$gpPage) return;
		
		array_unshift($breadcrumb,$this->_getTitleFromPage($gpPage));
	}
	
	function _getTitleFromPage($titlePage) {
		$title = null;
		$titlePage->getComponents()->callFunction("title",array(&$title));
		$title = str_replace("<br/>"," ",$title);
		$title = strip_tags($title);
		$title = str_replace("&raquo;&nbsp;","",$title);
		return $title;
	}
}
?>