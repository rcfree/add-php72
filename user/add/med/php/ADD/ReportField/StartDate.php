<?php

class ADD_ReportField_StartDate extends ADD_ReportField_EncounterCol {
	function getDisplayText($page, $id) {
		$start = parent::getDisplayText($page,$id);
		if ($start=="Date known") {
			return "";
		}
		else {
			return $start;
		}	
	}
}
?>