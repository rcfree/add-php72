<?php

class ADD_ReportField_EncounterCreateDate extends ADD_ReportField {
	protected $_value=null;
	function populateValue($values, $page, $clearFirst=false) {
		if (!isset($values['!date_created'])) return;
		$this->_value = ADD_Util::dateFromISOToDMY($values['!date_created']);
	}	
	function getDisplayText($page, $id) {
		return $this->_value;
	}
}
?>