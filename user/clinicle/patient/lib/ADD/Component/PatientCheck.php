<?php

class ADD_Component_PatientCheck extends ADD_Component {
	function beforeSubmit(&$values) {
		$add = ADD::sys();

		$pdb = ADD::db('patient');
		if (isset($values[ADD_Component::PATIENT_ID])) $add->setPatientId($values[ADD_Component::PATIENT_ID]);
		
		$patientId = $add->getPatientId();
		
		if (!$patientId) throw new ADD_PatientException("No patient ID was provided to the page");
		if (!$pdb->doesPatientExist($patientId)) throw new ADD_PatientException("A patient with id ".$patientId." was not found. Please contact the administrator.");
    }

	function beforeGenerate() {
		$add = ADD::sys();
        $page = $this->_page;
		$patientId = $add->getPatientId();
		$page->html()->addElement('input',array('type'=>'hidden','value'=>$patientId,'name'=>ADD_Component::PATIENT_ID));
	}

	function beforePopulate() {
		$add = ADD::sys();
		$patientId = $add->getPatientId();
		$pdb = ADD::db('patient');
		
		if (!$patientId) throw new ADD_PatientException("No patient ID was provided to the page");
		if (!$pdb->doesPatientExist($patientId)) throw new ADD_PatientException("A patient with id ".$patientId." was not found. Please contact the administrator.");
	}
	
}
