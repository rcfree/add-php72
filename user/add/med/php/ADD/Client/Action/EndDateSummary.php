<?php

class ADD_Client_Action_EndDateSummary implements ADD_Client_Action_EncounterGridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("span","",array("ng-bind-html"=>"row.current_status.value == 2 ? row.end_date_known.value ? row.end_date.label : 'Unknown' : 'Active'"));
		return $html;
	}
	function getColumnHeading() {
		return "End date";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>