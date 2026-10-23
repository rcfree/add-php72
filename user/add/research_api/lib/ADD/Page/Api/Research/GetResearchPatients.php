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

class ADD_Page_Api_Research_GetResearchPatients extends ADD_Page_Api_Research {
	const CONSENT_ENCTYPE="res-general-consent";
	const DETAILS_ENCTYPE="res-contact-details";
	const REC_DB_OBSFIELD="add_to_research_db";
	const RID_OBSFIELD="ilh_identifier";
	
    function apiOutput() {
    	$edb = ADD::db('encounter');
		if(!$this->getPermission("view")) throw new ADD_NoPermissionToAccessException("No permission to view the research patients");
		
		//get all patients with consent record = Y
		$encForm = $edb->getEncounterFormWithType(self::CONSENT_ENCTYPE);
		$obsFields = $edb->getObservationFieldsByIdAndName($encForm['id'],true);
		$recDbConsent = $obsFields[self::REC_DB_OBSFIELD];
		$consentList = $edb->fetchAll("
		select p.identifier as identifier, (select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=consent.id and of.name=?
			) as research_id
from encounter consent 
left join patient p on consent.patient_id = p.id
left join encounter_form consent_form on consent.encounter_form_id = consent_form.id
where consent_form.type=? and consent.is_deleted!=1
group by consent.patient_id
having left(group_concat((select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=consent.id and of.name=?
			) order by (select o.value from observation o 
				left join observation_field of on o.observation_field_id=of.id
				where o.encounter_id=consent.id and of.name='consent_form_date'
			) desc),1)='Y'",array(self::RID_OBSFIELD,self::CONSENT_ENCTYPE,self::REC_DB_OBSFIELD));
		
		ADD::web()->addMessage("Success");
    	return array("data"=>$consentList);
	}
	function getExpectedPermissions() {
		return array('view');
	}
}
