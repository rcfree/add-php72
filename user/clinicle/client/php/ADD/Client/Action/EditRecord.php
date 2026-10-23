<?php

class ADD_Client_Action_EditRecord implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$title = $this->_rowPage->getSetting('title');
		$pim = $this->_rowPage->getModule()->getName().":".$this->_rowPage->getName();
		$html->addTextElement("a", "<i class='icon-edit icon-white'></i>&nbsp;".$this->_rowPage->getSetting('EditRecord.button_text',"Edit.."), array("class"=>"btn btn-small btn-success","href" => "javascript:;","ng-click" => "editItem('$title',row.id,'$pim')"));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>

		