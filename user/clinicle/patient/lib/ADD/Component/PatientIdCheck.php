<?php

class ADD_Component_PatientIdCheck extends ADD_Component implements ADD_Component_AjaxInterface {
	public function beforeSubmit(&$values) {
		$this->ajaxFunction($values,$this->_page);
	}
	public function ajaxFunction($values, ADD_Page $callPage) {
		$patientId = ADD::sys()->getPatientId();
		$sdb = ADD::db('search_patient');
       
		$patExists = $sdb->doPatientsExistWithIdentifiers($values);
		if (!$patExists) return;
		
		$existingPatInfo = $sdb->findExistingPatientIds($values, $patientId);
		
		foreach($existingPatInfo as $singlePatId => $singlePatInfo) {
			$this->_generateSinglePatientMessage($singlePatId,$singlePatInfo,$values);
		}
	}
	
	protected function _generateSinglePatientMessage($singlePatId, $singlePatInfo, $values) {
		$identifier = ADD_Util::val('identifier',$values);
		$nhsNo = ADD_Util::val('nhs_no',$values);
		$sitePath = ADD::sys()->config('site_path');
		$patLink = "{$sitePath}/patient/demographics?pid=". $singlePatId;
		$page = $this->_page;
        $link = new ADD_Content_Html();
        $link->addTextElement("a","Go to patient record in ADD",array("ng-click"=>"gotoPage('$patLink')"));
        
		if (in_array('identifier',$singlePatInfo) && in_array('nhs_no',$singlePatInfo)) {
			throw new ADD_AjaxFieldException("A patient with S number '{$identifier}' and NHS number '{$nhsNo}' already exists in ADD.<br/>".$link->output(),"","identifier");
		}
		elseif (!ADD_Util::isEmpty($identifier) && in_array('identifier',$singlePatInfo)) {
			throw new ADD_AjaxFieldException("A patient with S number '{$identifier}' already exists in ADD.<br/>".$link->output(),"","identifier");
		}
		elseif (!ADD_Util::isEmpty($nhsNo) && in_array('nhs_no',$singlePatInfo)) {
			throw new ADD_AjaxFieldException("A patient with NHS number '{$nhsNo}' already exists in ADD.<br/>".$link->output(),"","nhs_no");
		}
	}	
	public function ajaxInputs(ADD_Page $callPage) {
		return array("identifier","nhs_no");
		}

}
?>