<?php

class ADD_Component_CheckRidUnique extends ADD_Component implements ADD_Component_AjaxInterface {
    function ajaxFunction($values,ADD_Page $callPage) {
        $val = ADD_Util::val('action_study_no',$values);
        if(ADD_Util::isEmpty($val)) return;
		$parentId = ADD_Util::val('__parent',$values);
		if(ADD_Util::isEmpty($parentId)) return;
		
        $edb = ADD::db('encounter');
        $em = ADD::sys()->getEncounterManager();
		
		$parentEr = $em->sectionById($parentId);
        $parentEr->retrieve();
		$currentEr = $em->child($parentEr,"res-study-details");
		$currentEr->retrieve();
		
		$study = $currentEr->getObsValue("study");
		if(ADD_Util::isEmpty($study)) throw new ADD_NullValueProvidedException("Study type must be selected");
		
		$encs = $edb->getEncountersWithObservationValue("res-study-details","study",$study);
		
		$scount = 0;
		if(!$encs) return;
		foreach($encs as $enc) {
			if($currentEr->getId() == $enc['id']) continue;
			$obs = $edb->getObservationsInEncounter($enc);
			if (ADD_Util::val('study_no',$obs)==$val) {
				$scount++;
			}
		}
		if ($scount>0) throw new ADD_AjaxFieldException("<i>$val</i> has already been used <b>$scount</b> ".ADD_Util::pluralize($scount,"time")." in this study","Study number","study_no");
    }

    function ajaxInputs(ADD_Page $callPage) {
        return array('action_study_no');
    }
} 