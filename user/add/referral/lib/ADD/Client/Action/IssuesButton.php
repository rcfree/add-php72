<?php

class ADD_Client_Action_IssuesButton implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return true;
	}
	function outputMarkup() {
		return '<button type="button" class="btn btn-small" title="{{row.issues.label}}" onclick="javascript:ADD.Notice.showAlert(\'Issues\',$(this).attr(\'title\'),\'Close\')"><i class=\'icon-list\'></i>&nbsp;Issues</button>';
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>