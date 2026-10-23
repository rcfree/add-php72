<?php

class ADD_Component_Encounter_Domain_ResearchManualEnrol extends ADD_Component_Encounter_Domain_ManualEnrol {
	function menuDomainAddOption($options) {
		$page = $this->_page;
		$sent = $this->getSentRecord();
		$showAddOption = true;
		
		$sent->retrieve();
		
		if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="N") $showAddOption=false;	
		
		if($showAddOption) return parent::menuDomainAddOption($options);   
		$this->addDomainMessageItem($options,"<i class='icon-info-sign'></i>&nbsp;Patient not interested","text text-error");
	}
	
	function _getMenuExtraInfo() {
		$research = $this->_page->getEncounterRecord();
		
		list($label,$class) = $this->_getLabelBasedOnSent($research,$this->getSentRecord());
		
		if($label) {
			return array($label,$class,false,null);
		}
		
		$detailsPage = $this->_page->getModule()->getPage('details');
		$details = $detailsPage->getEncounterRecord();
		$csVar = $details->getObsValue("current_status");
				
		if($csVar==3) $class="label-success";
		if($csVar==1 || $csVar==2 || $csVar==6)  $class="label-important";
		if($csVar==5) $class="label-warning";
		$currentStatus = $details->getObsText("current_status",$detailsPage);
		
		$em = ADD::sys()->getEncounterManager();
		$research->retrieve();
		$consents = $em->sectionsByPatientAndType($research->getPatient(),"res-general");
		
		$inResearchDb=null;
		
		if(!$csVar) {
			if($this->hasNoConsents($consents)) {
				return array("Status and consent not setup","label-warning",true,$inResearchDb);
			}
			else {
				$latestConsent = $this->getLatestConsentRecord($consents);
				$latestConsent->retrieve();
				$inResearchDb = $latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RESEARCH_DB_FIELD)=="Y";
				return array("Recruitment status not setup","label-warning",true,$inResearchDb);
			}
		}
		
		
		if($csVar && $this->hasNoConsents($consents)) {
			if ($csVar==3 || $csVar==5) {
				return array("Consent not setup","label-warning",true,$inResearchDb);
			}
		}
		else {
			$latestConsent = $this->getLatestConsentRecord($consents);
			$latestConsent->retrieve();
			$inResearchDb = $latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RESEARCH_DB_FIELD)=="Y";
				
			if($latestConsent->getObsValue(ADD_Constants_Research::ADD_TO_RECRUIT_DB_FIELD)!="Y") {
				$currentStatus = "No recruitment database consent";
				$class = "label-important";
			}
		}
		return array($currentStatus,$class, true,$inResearchDb);
	}
	
	function menuDomainTitleExtra($options) {
		list($label,$class, $isSmaller,$inResearchDb) = $this->_getMenuExtraInfo();
		
		if(!$label) return;
		$attrs = array("class"=>"label $class");
		
		if($isSmaller) {
			$attrs['style']="font-weight:normal;font-size:90%;";
			$options->startElement("div");
		}
		else {
			$options->addHtml("&nbsp;");
		}
		$options->addTextElement("span",$label,$attrs);
		$options->addHtml("&nbsp;");
		if($inResearchDb===true) {
			$attrs['class']="label label-success";
			$options->addTextElement("span","Research database consent given",$attrs);
		}
		elseif($inResearchDb===false) {
			$attrs['class']="label label-important";
			$options->addTextElement("span","No research database consent",$attrs);
		}
		if($isSmaller) {
			$options->endElement("div");
		}		
	}
	
	public function getLatestConsentRecord($dbConsents) {
		$add = ADD::sys();
		$em = $add->getEncounterManager();
		
		$consents = array();
		if(count($dbConsents)==0) return null;
		
		foreach($dbConsents as $dbConsent) {
			$consentDetails = $em->child($dbConsent,"res-general-consent");
			$consentDetails->retrieve();
			$consents[]=$consentDetails;
		}
		$errorReporting = error_reporting(); error_reporting(0);
		@usort($consents,function($a,$b) { 
			$startDate = $a->getObsValue(ADD_Constants_Research::CONSENT_DATE_FIELD);
			$endDate = $b->getObsValue(ADD_Constants_Research::CONSENT_DATE_FIELD);
			$endDate = DateTime::createFromFormat('d/m/Y',$endDate);
			$startDate = DateTime::createFromFormat('d/m/Y',$startDate);
			return $startDate < $endDate;
		});
		 error_reporting($errorReporting);
		return $consents[0];
	}
	/*
	function showConsentRequestStatus($options) {
		$sent = $this->getSentRecord();
		$sent->retrieve();
		$label=null;
		
		$class="label-warning";
		if($this->isDeceased()) {
			$label = "Deceased";
			$class="label-important";
		}
		else {
			if(!$sent->exists()) {
				$label="Not sent";
			}
			else {
				if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="Y") {
					$date = $sent->getObsValue('date_sent');
					$label = "Form sent";
					$class = "label-success";
				}
				else if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="N") {
					$label = "No interest";
					$class="label-important";
				}
				else {
					$label = "Not entered";
				}
			}
	}
		if($label) {
			$options->addHtml("&nbsp;");
			$options->addTextElement("span",$label,array("class"=>"label $class"));
		}
	}
	 * 
	 */
	function getSentRecord() {
		$cpage = $this->_page->getModule()->getPage('sent');
		$sent = $cpage->getEncounterRecord();
		return $sent;
	}
	
		
	public function hasNoConsents($dbConsents,$options=null) {
		if(count($dbConsents)==0) {
			return true;
		}
	}
	
	function _getLabelBasedOnSent($research, $sent) {
		if($this->isDeceased()) {
			return array("Deceased","label-important");
		}
		
		$sent->retrieve();
		
		if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="N") {
			return array("No interest","label-important");
		}
			
		if (!$research->exists()) {
			if (!$sent->exists()) {
				return array("Not sent","label-warning");
			}
			if($sent->getObsValue(ADD_Constants_Research::SENT_CONSENT_FIELD)=="Y") {
				return array("Form sent","label-success");
			}
			else {
				return array("Not entered","label-warning");
			}
		}
		return array(null,null);
	}
}
?>
