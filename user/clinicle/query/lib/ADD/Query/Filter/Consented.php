<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;

class ADD_Query_Filter_Consented extends ADD_Query_Filter_ObsValue {
	const RESEARCH="add_to_research_db";
	const RECRUIT="add_to_recruit_db";
	
	function __construct($attrs) {
		$perm = ADD_Util::val("consent",$attrs);
		if(!$perm) throw new Exception("consent attribute not provided");
		$attrs = array(
			"encounter"=>"res-general-consent",
			"field"=>$perm,
			"op"=>"any",
			"type"=>ADD_Query_Filter::PATIENT
		);
		parent::__construct($attrs);
    }
	function beforeFilter(ADD_Query_Result $result, ADD_Query_Run $run) {
		$run->addValue("ObsData",array("encounter"=>"res-general-consent","internal"=>true,"raw_values"=>true));
	}
	function filterAfterRetrieve(ADD_Query_ResultItem $resultItem) {
		$encsById = $resultItem->getEncounterData("res-general-consent");
		
		if (!$encsById || ADD_Util::isEmpty($encsById)) {
			$resultItem->setExcluded(true);
			return;
		}
		$encData = array_values($encsById);
		
		usort($encData,function($a,$b) {
			$startDate = ADD_Util::val('consent_form_date',$a);
			$endDate = ADD_Util::val('consent_form_date',$b);
			if($startDate==$endDate) return 0;
			$endDate = DateTime::createFromFormat('d/m/Y',$endDate);
			$startDate = DateTime::createFromFormat('d/m/Y',$startDate);
			return ($startDate < $endDate) ? -1 : 1;
		});
		$firstConsent = $encData[0];
		if(ADD_Util::val($this->getAttr("field"),$firstConsent)!="Y") {
			$resultItem->setExcluded(true);
		}
	}
}