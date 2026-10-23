<?php

class ADD_ReportField_EncounterMedTable extends ADD_ReportField_EncounterTable {
	/*
	function getRowEncounters($page) {
		$lo = $page->getLayout();
		$form = $page->getForm();
		$patientId= $lo->getPatientId();
		$db = $lo->getDatabase();
		
		$pencId = $lo->getQueryParam('id');
		$penc = $db->getEncounter($pencId);
		$subEncType = $this->getSetting('encounter');
		$encBits = explode(".",$subEncType);
		
		if ($this->getSetting('from_parent')) $penc = $penc->getParentEncounter();
		
		$cencs = $db->getChildEncountersWithPatientAndType($patientId, $penc, $subEncType);
		
		$rowEncounters = array();
		
		$data = $page->getData();
		list($fromDate,$toDate) = ADD_Util::getArrayValue(array('from_date','to_date'),$data);
		$fromDt = ADD_Util::dateFromDmy($fromDate);
		$toDt = ADD_Util::dateFromDmy($toDate);
		
		

		foreach($cencs as $cenc) {
			$obs = $db->getObservationsInEncounter($cenc);
			$adhType = ADD_Util::getArrayValue('adherence_type',$obs);
			/*
			if ($adhType=='1') {
				list($denc,$dobs) = $db->getLinkedEncounter($cenc,'drug_encounter_id');
				list($startDate,$endDate, $status) = ADD_Util::getArrayValue(array('start_date','end_date','current_status'),$dobs);
				$startDt = ADD_Util::dateFromDmy($startDate);
				$endDt = ADD_Util::dateFromDmy($endDate);
				if ($status=='1' && $startDt<$fromDt) continue;
				if ($status=='2' && $endDt<$toDt) continue;
			}
			else {
				$testTs = ADD_Util::getArrayValue('datetime',$obs);
				$testTsDt = ADD_Util::dateFromDmyHm($testTs);
			}

			$rowEncounters[$cenc->getId()] = $cenc;
		}
		return $rowEncounters;
	}
*/
}
?>