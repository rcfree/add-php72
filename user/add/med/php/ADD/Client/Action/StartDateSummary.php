<?php

class ADD_Client_Action_StartDateSummary implements ADD_Client_Action_EncounterGridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("span","",array("ng-bind-html"=>"row.start_date.value ? row.start_date.label : row.start_date_type.label"));
		return $html;
	}
	function getColumnHeading() {
		return "Start date";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>