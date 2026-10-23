<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_SpecificDrugs extends ADD_Query_Value_ObsData {

    function __construct($attrs,ADD_Query_Run $run) {
        $attrs = array(
        	"encounter"=>"med-general-drug",
			"fields"=>array("drug_readcode","drug_name","route","strength","formulation","dose","frequency","current_status"),
			"internal"=>true,
		);	
        parent::__construct($attrs,$run);
    }
	
	function addFieldsToResult(ADD_Query_Result $result) {
		parent::addFieldsToResult($result);
		$fieldMap = array("specific_drugs"=>"Specific drugs");
		$result->addPatientFieldMap($fieldMap);
	}
	function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
		parent::retrieveDataForPatient($resultItem,$run);
		$drugData = $resultItem->getEncounterData("med-general-drug");
		$activeDrugs=array();
		if(!$drugData || count($drugData)==0) return;
		$specificDrug=false;
		foreach($drugData as $drugId => $drug) {
			if($drug['current_status']!="Active") continue;
			if($this->isSpecificDrug($drug)) {
				$specificDrug=true;
				$activeDrugs[]=$drug['drug_name']." ".ADD_Util::val('strength',$drug)." ".ADD_Util::val('formulation',$drug)." (".ADD_Util::val('dose',$drug).")";
			}
		}
		if(!$specificDrug) {
			$resultItem->setExcluded(true);
			return;
		}
		$resultItem->addPatientData(array("specific_drugs"=>implode("\n",$activeDrugs)));
	}
	function isSpecificDrug($drug) {
		$drugName = ADD_Util::val('drug_name',$drug);
		$strength = ADD_Util::val('strength',$drug);
		$dose = ADD_Util::val('dose',$drug);
		$frequency = ADD_Util::val('frequency',$drug);
		if($frequency!="BD") return false;
		
		if($drugName=="SYMBICORT" && $strength=="200/6" && $dose>=2) return true;
		if($drugName=="SYMBICORT" && $strength=="400/12" && ($dose==1 || $dose==2)) return true;
		if($drugName=="SERETIDE" && $strength=="125" && $dose==2) return true;
		if($drugName=="SERETIDE" && $strength=="250" && $dose==2) return true;
		if($drugName=="SERETIDE" && $strength=="500" && $dose==1) return true;
		if($drugName=="FOSTAIR" && $strength=="100mcg/6mcg" && $dose>2) return true;
		
		return false;
	}
}