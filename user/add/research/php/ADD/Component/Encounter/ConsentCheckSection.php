<?php



class ADD_Component_Encounter_ConsentCheckSection extends ADD_Component_Encounter_Section {
	protected $noContact;
	
	public function menu($options) {
		$add = ADD::sys();
		$this->messages = array(
			ADD_Constants_Research::PATIENT_REQUEST_NO_CONTACT=>array("Patient requests no contact",true),
			ADD_Constants_Research::PATIENT_UNAVAILABLE_RECRUIT=>array("Patient not available",false),
			ADD_Constants_Research::PATIENT_UNSUITABLE_RECRUIT=>array("Patient unsuitable for research",false),
			ADD_Constants_Research::PATIENT_ENROL_BUT_NOT_INTERESTED=>array("Patient enrolled but not interested",false),
			ADD_Constants_Research::PATIENT_DECEASED=>array("Patient is deceased",true)
		);
		$em = $add->getEncounterManager();
		$pid = $add->getPatientId();
		
		$this->noContact = ADD::sys()->getPageInModule('research','general')->getComponent('Encounter')->doesPatientRequestNoContact();
		$researchPage = ADD::sys()->getPageInModule('research','research');
		$researchComp = $add->getPageInModule('research','research')->getComponent('Encounter');
		$research = $em->domain("res",$pid);
		
		$dbConsents = $em->sectionsByPatientAndType($pid,"res-general");
		
		if($researchComp->hasNoConsents($dbConsents,$options)) {
			return;
		}
		$latestConsent = $researchComp->getLatestConsentRecord($dbConsents);
		if($latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RECRUIT_DB_FIELD)!="Y") {
			return;
		}
		$message = ADD_Util::val($this->noContact,$this->messages);
		$this->noLinks = $message[1];
		
		parent::menu($options);
	}
	
}
?>