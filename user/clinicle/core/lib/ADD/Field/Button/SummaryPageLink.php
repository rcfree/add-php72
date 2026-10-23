<?php

class ADD_Field_Button_SummaryPageLink extends ADD_Field_Button {
	
	protected function getLinkPath($page) {
		$add = ADD::sys();	
		$summPageName = $page->getSetting('summary-section');
		if (!$summPageName) throw new ADD_FieldException("No 'summary-section' option provided in SummaryPage button field");
		$summPage = $page->getModule()->getPage($summPageName);
		if (!$summPage) throw new ADD_FieldException("Unable to find section '{$summPageName}' for SummaryPageLink");
		
		$query = array('pid'=>$add->getPatientId());
		if ($add->getParam('eid')) $query['eid']=$add->getParam('eid');
		$path = ADD_Util::uri($summPage->createMenuPath(),$query);
		return $path;
	}
	public function isAvailable($page) {
		if($page->getSetting('ignore_summary')) return null;
		return true;
	}
	
}
?>