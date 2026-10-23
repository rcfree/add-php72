<?php
use Clinicle\Search\LinkedRecordQuery;
use EMBER\Calculate\DECAFScore;

class ADD_Component_DECAFCalculate extends ADD_Component implements ADD_Component_AjaxInterface {
	function ajaxFunction($values, ADD_Page $callPage) {
		$add = ADD::sys();
		$pid = $add -> getPatientId();
		
		$ajaxInputs = $this->ajaxInputs($callPage);
		array_pop($ajaxInputs);
        $emrcPage = $callPage->getSetting("DECAF.emrc_page",$callPage->getName());
        $emrcField = $callPage->assertSetting('DECAF.emrc_field');

        list($mrc) = ADD_Field_Util::getSiblingObs($callPage, $emrcPage, array($emrcField), "calculate DECAF 
		score", $values);
        $measuresPage = $callPage->getSetting("DECAF.measures_page",$callPage->getName());
		list($cons, $rhythm) = ADD_Field_Util::getSiblingObs($callPage, $measuresPage, array("evidence_of_consolidation","ecg_rhythm"), "calculate DECAF 
		score", $values);
        $bloodTestPage = $callPage->getSetting("DECAF.blood_test_page",$callPage->getName());
        $eos = ADD_Field_Util::getSiblingObs($callPage, $bloodTestPage, array(
            "blood_value_eosab"),
            "calculate DECAF score", $values);
        $ph = ADD_Field_Util::getSiblingObs($callPage, $bloodTestPage, array(
            "ph"),
            "calculate DECAF score", $values,true);
        
        $decaf = new DECAFScore();
		$score = $decaf->determineScore($mrc,$eos,$cons,$ph,$rhythm);
		return array("decaf_score"=>$score);
	}

	function ajaxInputs(ADD_Page $callPage) {
		$emrcField = $callPage->assertSetting('DECAF.emrc_field');
		return array($emrcField,"evidence_of_consolidation","ecg_rhythm");
	}
}
