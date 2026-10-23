<?php

class ADD_Client_Action_DateCreated implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return true;
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addHtml("{{row.date_created | timeToDate}}");
		return $html;
	}
	function getColumnHeading() {
		return "Date recorded";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>