<?php

class ADD_Client_Action_StatusAction implements ADD_Client_Action_EncounterGridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$pim = $this->_rowPage->getModule()->getName().":".$this->_rowPage->getName();
		$class = "row.is_complete == 1 ? 'icon-white icon-eye-open' : 'icon-white icon-edit'";
		$title = $this->_rowPage->getSetting('title');
		$html->startElement("button",array("style"=>"width:8em","ng-class"=>"'btn btn-small ' + (row.is_complete == 1 ? 'btn-success' : 'btn-warning')","type"=>"button","ng-cloak"=>"","ng-click"=>"row.is_complete == 1 ? viewItem('Read ".$title."',row.id,'$pim') : editItem('Continue ".$title."',row.id,'$pim')"));
		$html->addTextElement("i","",array("ng-class"=>$class));
		$html->addHtml("{{ row.is_complete == 1 ? '&nbsp;Read..' : '&nbsp;Continue..'}}");
		$html->endElement("button");
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>