<?php


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ADD\Med\ActiveDrugs\RangeActiveDrugs;

class ADD_Page_Api_DrugEncounters extends ADD_Page_Api_Encounters {
	function apiOutput() {
	    $output = parent::apiOutput();
        $fullData = $output['data'];
        $includeFields = explode(";",ADD::sys()->getParam('includefields'));
        $visit1Date = ADD::sys()->getParam($includeFields[0]);
		$visit2Date = count($includeFields)==2 ? ADD::sys()->getParam($includeFields[1]) :null;
        if(!$visit1Date) {
            ADD::web()->addWarning("Please enter '".ADD_Util::nameToLabel($includeFields[0])."' to display drug records below");
            return array();
        }
		$drugTimepoint = new RangeActiveDrugs($visit1Date, $visit2Date);
        $activeDrugs = $drugTimepoint->extractActiveRecords($fullData);
        $output['data']=$activeDrugs;
        return $output;
    }
    
}