<?php
class ADD_Data_HoldingService {
	
	function createPatientUsingIdentifier($identifier) {
		$pmi = ADD_Util::createPMI();
		$pmiPatient = $pmi -> get_patient_by_id($identifier);
		if(!$pmiPatient) throw new Exception("PMI patient with ID '$identifier' not found");
		$patientData = ADD_PatientImport_Util::mapPmiPatientFields($pmiPatient);
		list($patient,$addr,$gp) = ADD::db('patient')->createPatientDemographics($patientData);
		return $patient;
	}
	function addReferralRecordToPatient($patient,$encType,$obsValues) {
		$em = ADD::sys()->getEncounterManager();
		$referral = $em->record($encType,$patient['id']);
        $referral->setNew(true);
		$referral->setObsValues($obsValues);
		$referral->store();
	}
	function moveRelatedRecordsToPatient($patient,$encounterId, $pim, $updateObs=array()) {
		$heldPage = ADD::sys()->getPageFromPim($pim);
		$heldParentPage = $heldPage->getParent();
		if(!$heldParentPage) {
			$this->_reassignRecordToPatient($encounterId,$patient);
			return;
		}
		if(!$heldParentPage->hasComponent('Encounter')) return;
		$heldParentEncounter = $heldParentPage->getComponent('Encounter');
		
		if(is_a($heldParentEncounter,'ADD_Component_Encounter_Domain')) {
			$this->_attachRecordDirectlyToDomain($heldParentPage,$heldPage,$patient,$encounterId,$updateObs);
			return;
		}
		if(is_a($heldParentEncounter,'ADD_Component_Encounter_RepeatSection')) {
			$this->_attachSectionRecordToDomain($heldParentPage,$heldPage,$patient,$encounterId,$updateObs);
			return;
		}
	}
	function _reassignRecordToPatient($encounterId,$patient) {
		$em = ADD::sys()->getEncounterManager();
		
		ADD::web()->addWarning("create separate record");
		$domain = $em->recordById($encounterId);
		$domain->retrieve();
		$domain->setEncData('patient_id',$patient['id']);
		$domain->store();
	}
	function _createParentRecord($parentPage, $patient, $obsValues=null) {
		$em = ADD::sys()->getEncounterManager();
		
		$domain = $em->domain($parentPage->getSetting('encounter'),$patient['id']);
		$domain->retrieve();
		if($obsValues) {
			$domain->setObsValues($obsValues);
		}
		if(!$domain->exists()) {
			$domain->store();
		}
		return $domain;
	}
	function _attachSectionRecordToDomain($parentPage,$page,$patient, $encounterId,$updateObs) {
		$domainPage = $parentPage->getParent();
		$domain = $this->_createParentRecord($domainPage,$patient);
		
		$em = ADD::sys()->getEncounterManager();
		
		$detail = $em->recordById($encounterId);
		$detail->retrieve();
		$detail->setType($page->assertSetting('encounter'));
		
		if(!$detail->exists()) throw new Exception("The record with ID $encounterId was not found");
		if($updateObs) $detail->store($updateObs);
		
		$section = $detail->getParent();
		$section->retrieve();
		if(!$section->exists()) throw new Exception($page->getSetting('title')." has no containing ".$parentPage->getSetting('title'));
		ADD::db('encounter')->execute("update encounter set patient_id=? where parent_encounter_id=?",array($patient['id'],$section->getId()));
		
		$section->setEncData("patient_id",$patient['id']);
		$section->store();
		ADD::web()->addMessage("Record successfully moved to ".$domainPage->getSetting('title')." domain and linked to patient <a href='/?pid=".$patient['id']."&t=".ADD_Util::token()."'>".$patient['first_name']." ".$patient['last_name']." (".$patient['identifier']."</a>");
	}

	function _attachRecordDirectlyToDomain($parentPage,$page,$patient, $encounterId, $updateObs) {
		$em = ADD::sys()->getEncounterManager();
		
		$per = $this->_createParentRecord($parentPage,$patient);
		
		$er = $em->recordById($encounterId);
		$er->retrieve();
		if(!$er->exists()) throw new Exception("The record with ID $encounterId was not found");
		$er->setEncData("parent_encounter_id",$per->getId());
		$er->setEncData("patient_id",$patient['id']);
		$er->store($updateObs);
		ADD::web()->addMessage("Record successfully moved to ".$parentPage->getSetting('title')." domain and linked to patient <a href='/?pid=".$patient['id']."&t=".ADD_Util::token()."'>".$patient['first_name']." ".$patient['last_name']." (".$patient['identifier'].")</a>");
	}
	
	function _createSeparateRecord($patient, $recordPage, $obsValues) {
		$em = ADD::sys()->getEncounterManager();
		
		$er = $this->_createParentRecord($recordPage,$patient,$obsValues);
		
		ADD::web()->addMessage("Record successfully linked to patient ".$patient['first_name']." ".$patient['last_name']." (".$patient['identifier'].")");
	}
}
