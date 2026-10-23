<?php
require_once("ADD/ReportField/EncounterCol.php");
class ADD_ReportField_EndDate extends ADD_ReportField_EncounterCol {
	function getDisplayText($page, $id) {
		$end = $this->getField()->getValue();
		if ($end=="N") return "Unknown";
	}
}
?>