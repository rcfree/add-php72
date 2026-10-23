<?php



class ADD_Component_Encounter_DbConsentRepeatSection extends ADD_Component_Encounter_RepeatSection {
	
	private $patientRequestsNoContact;
	
	public function menu($options) {
		$this->patientRequestsNoContact = $this->doesPatientRequestNoContact();
		
		$create = $this->_page -> getPermission('create');
		//if ($create || $cerCount > 0) {
		//	$this->menuSectionTitle($options,$cerCount);
		//}
		//$options->addHtml($items);
		
		if ($create) {
			$this->menuSectionAdd($options);
		}

		//$options -> endElement("ul");
	}
	
	public function menuSectionItems() {
		$noContact = $this->patientRequestsNoContact;
		return array(0,array());
		list($count,$items) = parent::menuSectionItems();
		if (!$noContact) return array($count,$items);
		
		return array(0,$items);
	}
	public function menuSectionAdd($options) {
		$noContact = $this->patientRequestsNoContact;
		if (!$noContact) return;// parent::menuSectionAdd($options);
		if ($noContact!=ADD_Constants_Research::PATIENT_DECEASED && $noContact!=ADD_Constants_Research::PATIENT_STATUS_NOT_SET && $noContact!=ADD_Constants_Research::PATIENT_NO_IDENTIFIER && $noContact!=ADD_Constants_Research::PATIENT_ENROL_BUT_NOT_INTERESTED) {
			return;
		}
		//	parent::menuSectionAdd($options);
		
		$messages = array(
			ADD_Constants_Research::PATIENT_STATUS_NOT_SET=>"Research status not set",
			ADD_Constants_Research::PATIENT_REQUEST_NO_CONTACT=>"Requests no contact",
			ADD_Constants_Research::PATIENT_UNSUITABLE_RECRUIT=>"Unsuitable for recruitment",
			ADD_Constants_Research::PATIENT_ENROL_BUT_NOT_INTERESTED=>"Enrolled in research<br/> but marked as not interested",
            ADD_Constants_Research::PATIENT_NO_IDENTIFIER=>"Research/ILH identifier not setup",
			ADD_Constants_Research::PATIENT_DECEASED=>"Patient is deceased"
		);
		$message = ADD_Util::val($noContact,$messages);
		if($noContact && !$message) return;
		
		$options->startElement("span",array("class"=>"label label-warning"));
		$options->addTextElement("i","",array("class"=>"icon-white icon-exclamation-sign"));
		
		$options->addHtml("&nbsp;".$message);
		$options->endElement("span");
	}
	public function doesPatientRequestNoContact() {
		$add = ADD::sys();
		$contactPage = $this->_page->getModule()->getPage('details');
		$sentPage = $this->_page->getModule()->getPage('sent');
		
		$contact = $contactPage->getEncounterRecord();
		$contact->retrieve();
		$researchPage = $this->_page->getModule()->getPage('research');
		if($researchPage->getComponent('Encounter')->isDeceased()) {
			return ADD_Constants_Research::PATIENT_DECEASED;
		}
		$initStatus = $contact->getObsValue(ADD_Constants_Research::CURRENT_STATUS_FIELD);
		if(!$initStatus) return ADD_Constants_Research::PATIENT_STATUS_NOT_SET;
		if ($initStatus==2) return ADD_Constants_Research::PATIENT_REQUEST_NO_CONTACT;
		
		if(strlen($contact->getObsValue(ADD_Constants_Research::ID_FIELD))==0) return ADD_Constants_Research::PATIENT_NO_IDENTIFIER;
		if ($initStatus==1) return ADD_Constants_Research::PATIENT_UNSUITABLE_RECRUIT;
		if ($initStatus==5) return ADD_Constants_Research::PATIENT_UNAVAILABLE_RECRUIT;
		
		$sent = $sentPage->getEncounterRecord();
		$sent->retrieve();
		if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="N") return ADD_Constants_Research::PATIENT_ENROL_BUT_NOT_INTERESTED;
	}
}
?>