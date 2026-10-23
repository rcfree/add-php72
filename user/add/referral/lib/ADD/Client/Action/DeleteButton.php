<?php

class ADD_Client_Action_DeleteButton implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('delete');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("a", "<i class='icon-fire'></i>&nbsp;Delete..", array("class"=>"btn btn-warning btn-mini","href" => "javascript:;", "ng-click"=>"delete(row.id)"));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>