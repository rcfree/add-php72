<?php

class ADD_Client_Action_EditDictItem implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$menuPath = $this->_rowPage->createMenuPath();
		$rowPage = $this->_rowPage;
		$pim = $rowPage->getModule()->getName().":".$rowPage->getName();
		$html->addTextElement("a", "<i class='icon-pencil'></i>&nbsp;Edit..", array("class"=>"btn btn-mini","ng-click" => "editItem('Edit consultant',row.id,'$pim')"));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}


		