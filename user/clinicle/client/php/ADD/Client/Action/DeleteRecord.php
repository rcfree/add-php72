<?php

class ADD_Client_Action_DeleteRecord implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		if(ADD::sys()->hasState(ADD_System::READONLY)) return false;
		if(ADD::sys()->hasState('locked')) return false;
		
		return $this->_rowPage->getPermission('delete');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$showMsg = $this->_rowPage->getSetting('DeleteRecord.hide_message') ? 'true' : 'false';
		$html->addTextElement("a", "<i class='icon-remove icon-white'></i>&nbsp;Delete..", array("class"=>"btn btn-small btn-warning ignoredirty","href" => "javascript:;", "ng-click"=>"deleteItem(row.id,$showMsg)"));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>