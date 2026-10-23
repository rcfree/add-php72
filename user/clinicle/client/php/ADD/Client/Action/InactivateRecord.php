<?php

class ADD_Client_Action_InactivateRecord implements ADD_Client_Action_EncounterGridRowActionInterface, ADD_Client_Action_GridFieldContainerInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
        $inactivatePage = $this->getGridField()->getSetting("InactivateRecord.pim","med:inactivate");
		$html->addTextElement("a", "<i class='icon-pencil'></i>&nbsp;Inactivate..", array("class"=>"btn btn-small","href" => "javascript:;", "ng-hide"=>"row.current_status.value==2","ng-click" => "editItem('Inactivate drug',row.id,'$inactivatePage')"));
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		//$html = new ADD_Content_Html();
		//$rowPage = $this->_rowPage;
		//
		//return $html;
	}
    function getGridField() {
        return $this->gridField;
    }
    function setGridField($gridField) {
        $this->gridField = $gridField;
    }
}
?>