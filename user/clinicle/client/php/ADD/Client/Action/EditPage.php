<?php

class ADD_Client_Action_EditPage implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$menuPath = $this->_rowPage->createMenuPath();
		$menuUri = $menuPath.'?pid={{row.patient_id}}&eid={{row.id}}';
		$html->addTextElement("a", "<i class='icon-pencil icon-white'></i>&nbsp;".$this->_rowPage->getSetting('EditPage.button_text',"Edit.."), array("class"=>"btn btn-inverse btn-small","href" => $menuUri));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
}
?>