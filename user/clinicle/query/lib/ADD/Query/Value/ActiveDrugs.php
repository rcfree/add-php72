<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_ActiveDrugs extends ADD_Query_Value_ObsData {

    function __construct($attrs,ADD_Query_Run $run) {
        $attrs = array(
        	"encounter"=>"med-general-drug",
			"fields"=>array("drug_name","route","strength","formulation","dose","current_status","frequency"),
			"internal"=>true,
		);	
        parent::__construct($attrs,$run);
    }
	
	function addFieldsToResult(ADD_Query_Result $result) {
		parent::addFieldsToResult($result);
		$fieldMap = array("active_drugs"=>"Active drugs");
		$result->addPatientFieldMap($fieldMap);
	}
	function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
		parent::retrieveDataForPatient($resultItem,$run);
		$drugData = $resultItem->getEncounterData("med-general-drug");
		$activeDrugs=array();
		if(!$drugData || count($drugData)==0) return;
		foreach($drugData as $drugId => $drug) {
			if($drug['current_status']!="Active") continue;
			$activeDrugs[]=$drug['drug_name']." ".ADD_Util::val('strength',$drug)." ".ADD_Util::val('formulation',$drug). "[".ADD_Util::val('dose',$drug)."] ".ADD_Util::val('frequency',$drug);
		}
		$resultItem->addPatientData(array("active_drugs"=>implode("\n",$activeDrugs)));
	}
}