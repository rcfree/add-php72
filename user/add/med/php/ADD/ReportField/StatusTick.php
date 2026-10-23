<?php

class ADD_ReportField_StatusTick extends ADD_ReportField_EncounterCol {
	function getDisplayText($page, $id) {	
		$status = parent::getField()->getValue();
		if ($status==2) return "X";
		return "";
	}
}
?>