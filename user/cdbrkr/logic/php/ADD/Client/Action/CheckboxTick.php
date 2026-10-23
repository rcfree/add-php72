<?php

class ADD_Client_Action_CheckboxTick implements ADD_Client_Action_GridRowActionInterface, ADD_Client_Action_GridFieldContainerInterface {
	private $field;
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return true;
	}
	
	function outputMarkup() {
		$cboxField = $this->getGridField()->assertSetting("CheckboxTick.field");
		$html = new ADD_Content_Html();
		$html->addHtml("<i class=\"{{ row.".$cboxField.".value == 1 ? 'icon-ok' : '' }}\"></i>");
		return $html;
	}
	function getColumnHeading() {
		return $this->getGridField()->getSetting("CheckboxTick.heading","Checkbox");
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
	function getGridField() {
		return $this->field;
	}

	function setGridField($field) {
		$this->field = $field;
	}
}
?>
