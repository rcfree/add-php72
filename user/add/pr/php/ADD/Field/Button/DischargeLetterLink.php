<?php

class ADD_Field_Button_DischargeLetterLink extends ADD_Field_Button_ReportLink {
	private $_reportName=null;
	protected function getOnClick($page) {
		$this->setSetting('report',$this->getReportName($page));
		return parent::getOnClick($page);
	}
	public function isAvailable($page) {
		$reportPage = $page->getModule()->getPage($this->getReportName($page));
		if(!$reportPage->getPermission('print')) return false;
		return true;
	}
	
	private function getReportName($page) {
		if($this->_reportName) return $this->_reportName;
		$reportName="pr-discharge-letter-consultant";
		$subjectPage = $page->getSiblingPage("referral");
		$subject = $subjectPage->getEncounterRecord(ADD::sys()->getParam('eid'));
		if($subject->exists()) {
			$subject->retrieve();
			if ($subject->getObsValue('referred_by')==1) $reportName="pr-discharge-letter-gp"; 
		}
		$this->_reportName=$reportName;
		return $reportName;
	}
}
?>