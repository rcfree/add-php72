<?php

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of Patient
 *
 * @author rcf8
 */

class ADD_Page_Api_Research_GetRcRids extends ADD_Page_Api_Research {
	
    function apiOutput() {
    	$projName = ADD::sys()->getParam('redcap_project');
		if($projName=="Bronchiecstasis") {
			return array(
				"data"=>array(
					array("patient_id"=>"125","research_id" => "ILH13770111","redcap_id"=>"11199.47"),
					array("patient_id"=>"129","research_id" => "ILH70077454","redcap_id"=>"11199.51")
				)
			);
		}
		else {
			return array(
				"data"=>array(
					array("patient_id"=>"125","research_id" => "ILH70077454","redcap_id"=>"1001"),
					array("patient_id"=>"129","research_id" => "ILH09222392","redcap_id"=>"1002")
				)
			);
		}
	}
	function getExpectedPermissions() {
		return array('view');
	}
}
