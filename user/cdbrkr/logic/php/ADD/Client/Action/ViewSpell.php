<?php

class ADD_Client_Action_ViewSpell implements ADD_Client_Action_GridRowActionInterface {
	function __construct(ADD_Page $rowPage) {
		$this->_rowPage = $rowPage;
	}
	function isAvailable() {
		return $this->_rowPage->getPermission('edit');
	}
	
	function outputMarkup() {
		$html = new ADD_Content_Html();
		$html->addTextElement("a", "Diagnoses..", array("class"=>"ignoredirty btn btn-inverse","href"=>"/cdbrkr/wspell/diagnoses?pid={{ row.patient_id }}&eid={{ row.parent_encounter_id }}"));
		
		return $html;
	}
	function getColumnHeading() {
		return "";
	}
	function afterOutputMarkup(ADD_Content_Html $html) {
		
	}
	function getNgClick() {
		return "primary(row.id,row.primary_diagnosis.value)";
	}
}
?>
