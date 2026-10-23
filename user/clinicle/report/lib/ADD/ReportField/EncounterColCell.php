<?php

class ADD_ReportField_EncounterColCell extends ADD_ReportField {
	function getDisplayText($page, $id) {
		$form = $page->getComponent("Form");
		$childFields = $form->getChildFields($this->getName());
		$text = array();
		foreach($childFields as $childField) {
			$text[]=$childField->getDisplayText($page,$id);
		}
		$joinChar = $this->getSetting('join-char'," ");
		return implode(" ",$text);
	}
	
	function populateValue($values, $page, $ignoreNew=false) {
		$form = $page->getComponent("Form");
		$childFields = $form->getChildFields($this->getName());
		foreach($childFields as $childField) {
			$childField->populateValue($values,$page,$ignoreNew);
		}
	}
}
?>