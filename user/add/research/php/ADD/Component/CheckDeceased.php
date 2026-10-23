<?php

class ADD_Component_CheckDeceased extends ADD_Component {
    function afterPopulate() {
		list($recDeceased,$patDeceased,$patient) = $this->_getDeceasedStates();
		if(!$patDeceased) return;
		
		if($recDeceased) return;
		
		$form = $this->_page->getComponent('Form');
		$status = $form->getField('current_status');
		$status->setValue(6);
		$status->setSetting('readonly',1);
		
		ADD::web()->addWarning("Patient recruitment status has been automatically set to 'Patient is deceased'");
	}	
	
	function afterSubmit(ADD_Page $submitPage) {
		if($submitPage!==$this->_page) return;
		
		list($recDeceased,$patDeceased,$patient) = $this->_getDeceasedStates();
		
		if ($recDeceased && $patDeceased) return;
		
		if ($recDeceased && !$patDeceased) {
			$patient['deceased']=1;
			ADD::db()->update("patient", $patient['id'],array("deceased"=>1),"date_updated");
			ADD::web()->addWarning("Patient recruitment status is deceased so their demographics status has also been changed.");
		}
	}
	
	private function _getDeceasedStates() {
		$details = $this->_page->getEncounterRecord();	
		$currStatus = $details->getObsValue('current_status');
		
		$patient = ADD::db('patient')->getPatient(ADD::sys()->getPatientId());
		$recDeceased = $currStatus==6 ? true : false;
		$patDeceased = ADD_Util::val('deceased',$patient,false);
		return array($recDeceased,$patDeceased, $patient);
	}
} 