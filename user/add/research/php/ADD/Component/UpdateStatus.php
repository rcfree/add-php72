<?php

class ADD_Component_UpdateStatus extends ADD_Component {
    function submit($values) {
		$add= ADD::sys();
		$pid = $add->getPatientId();
		$patient = ADD::db('patient')->getPatient($pid);
		
		
		$researchPage = $add->getPageInModule('research','research');
		$detailsPage = $add->getPageInModule('research','details');
		$contactPage = $detailsPage->getParentPage();
		$contact = $contactPage->getEncounterRecord();
		
		$details = $detailsPage->getEncounterRecord();
		$details->retrieve();
		$em = $add->getEncounterManager();
		if(ADD_Util::val('deceased',$patient)) {
			
			ADD::web()->addWarning("Patient recruitment status has been automatically set to 'Patient is deceased'");
			$details->store(array('current_status'=>6));
			$statusLog = $em->newChild($contact,"res-contact-status");
			$statusLog->store(array(
				"recruit_status"=>6,
				"change_comments"=>"Automatically set: patient is deceased",
				"log_type"=>1,
				"date_changed"=>ADD_Field_Util::today(),
			));
			return;
		}

		$consents = $em->sectionsByPatientAndType($pid,"res-general");
		if (count($consents)==0) return;

		$latestConsent = $researchPage->getComponent('Encounter')->getLatestConsentRecord($consents);
		
		$statusLog = $em->newChild($contact,"res-contact-status");
		if($latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RECRUIT_DB_FIELD)=="Y") {
			$details->store(array('current_status'=>3));
			$statusLog->store(array(
				"recruit_status"=>3,
				"change_comments"=>"Automatically set: latest recruitment DB consent record response is Yes",
				"log_type"=>1,
				"date_changed"=>ADD_Field_Util::today(),
			));
			ADD::web()->addWarning("Using the latest consent record (dated ".$latestConsent->getObsValue('consent_form_date').") the recruitment status has been automatically set to 'Available for recruitment'.");
			
		}
		else {
			
			$details->store(array('current_status'=>5));
			$statusLog->store(array(
				"recruit_status"=>5,
				"change_comments"=>"Automatically set: latest recruitment DB consent record response is No",
				"log_type"=>1,
				"date_changed"=>ADD_Field_Util::today(),
				"reason_unavail"=>4
			));
			ADD::web()->addWarning("Using the latest consent record (dated ".$latestConsent->getObsValue('consent_form_date').") the recruitment status has been automatically set to 'Not currently available'.");
			
		}
	}		
} 