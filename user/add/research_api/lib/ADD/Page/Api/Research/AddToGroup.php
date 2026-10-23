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

class ADD_Page_Api_Research_AddToGroup extends ADD_Page_Api_Research {
	const CONSENT_ENCTYPE="res-general-consent";
	const DETAILS_ENCTYPE="res-contact-details";
	const REC_DB_OBSFIELD="add_to_research_db";
	const RID_OBSFIELD="ilh_identifier";
	
    function apiOutput() {
    	$edb = ADD::db('encounter');
		$gdb = ADD::db('group');

		if(!$this->getPermission("create")) throw new ADD_NoPermissionToAccessException("No permission to add to a research group");
		$ridList = ADD::sys()->getParam('research_id_list');
		if(!$ridList) throw new ADD_NullValueProvidedException("No 'research_id_list' parameter provided");
		$groupName = ADD::sys()->getParam('group_name',"Untitled group ".date("d/m/Y H:i.s"));
		$rids = explode(",",$ridList);
		
		$encForm = $edb->getEncounterFormWithType(self::DETAILS_ENCTYPE);
		$obsFields = $edb->getObservationFieldsByIdAndName($encForm['id'],true);
		$researchId = $obsFields[self::RID_OBSFIELD];
		$web = ADD::web();
		$successCount=0;
		$failCount = 0;
		
		if (count($rids)==0) {
			throw new ADD_NullValueProvidedException("'research_id_list' parameter does not contain any IDs");
		}
		$group = $gdb->createGroup($groupName,null);
		foreach($rids as $rid) {
			$patient = $edb->fetchFirst("select p.* from encounter e left join patient p on e.patient_id = p.id left join observation o on e.id = o.encounter_id where e.encounter_form_id=? and o.observation_field_id=? and o.value=?",array($encForm['id'],$researchId['id'],$rid));
			if(!$patient) {
				$web->addWarning("Unable to find patient with research ID '$rid' in ADD");
				$failCount++;
				continue;
			}
			$gdb->addPatientToGroup($group,$patient);
			$successCount++;
		}
		if($successCount==0) {
			$gdb->rollbackTxn();
			throw new ADD_NullValueProvidedException("Failed to add any patients to group '$groupName'");
			
		}
		else {
			$web->addMessage("Successfully added $successCount ".ADD_Util::pluralize($successCount,"patient")." to group '$groupName'");
		}
    	if($failCount>0) {
    		$web->addWarning("Failed to add $failCount ".ADD_Util::pluralize($failCount,"patient") ." to group '$groupName'");
		}

    	return array("data"=>array("group"=>$group));
	}
	function getExpectedPermissions() {
		return array('create');
	}
}
