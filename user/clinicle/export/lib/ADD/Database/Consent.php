<?php
class ADD_Database_Consent extends ADD_Database {
    function createConsentRecord($includeNonConsents=false) {
        ADD::log($this) -> info("createConsentRecord");
        $this -> execute("drop temporary table if exists `consent_record`");
$sql = "
create temporary table `consent_record`
SELECT 
distinct
p.id as patient_id,
(select str_to_date(o.value,'%d/%m/%Y') from observation o 
    left join observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='consent_form_date'
) as 'consent_form_date',
(select o.value from observation o 
    left join observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='add_to_recruit_db'
) as 'add_to_recruit_db',
(select o.value from observation o 
    left join observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_general_consent.id and of.name='add_to_research_db'
) as 'add_to_research_db',
(select di.title from observation o 
    left join observation_field of on o.observation_field_id=of.id
    left join dictionary_item di on o.value = di.value and di.dictionary_id=84
    where o.encounter_id=res_contact_detail.id and of.name='current_status'
) as 'current_status',
(select o.value from observation o 
    left join observation_field of on o.observation_field_id=of.id
    where o.encounter_id=res_contact_detail.id and of.name='ilh_identifier'
) as 'ilh_identifier'
from patient p
".($includeNonConsents ? "left" : "inner")." join encounter as res_general_consent on p.id = res_general_consent.patient_id and res_general_consent.encounter_form_id=119 and res_general_consent.is_deleted != 1
inner join encounter as res_contact_detail on p.id = res_contact_detail.patient_id and res_contact_detail.encounter_form_id=139 and res_contact_detail.is_deleted != 1;
";
        $this -> execute($sql);
        $this -> execute("alter table consent_record add index patient_id(patient_id)");
    }

    function isPatientConsented($patientId) {
        $consent = $this -> fetchFirst("select add_to_research_db from consent_record where patient_id=? order by consent_form_date desc", $patientId);
        
        if ($consent['add_to_research_db'] == "Y") {
            //ADD::log($this) -> info("isPatientConsented:true");
        
            return true;
        } else {
            //ADD::log($this) -> info("isPatientConsented:false");
        
            return false;
        }
    }

    function getPatientIlhIdentifier($patientId) {
        $consent = $this -> fetchFirst("select ilh_identifier from consent_record where patient_id=?", $patientId);
        if(!$consent) {
            //ADD::log($this) -> info("getPatientIlhIdentifier:empty");
        
            return "";
        }
        //ADD::log($this) -> info("getPatientIlhIdentifier:".$consent['ilh_identifier']);
        
        return $consent['ilh_identifier'];
    }
    
    function getPatientRecruitmentStatus($patientId) {
        $consent = $this -> fetchFirst("select add_to_research_db, add_to_recruit_db, current_status from consent_record where patient_id=?", $patientId);
        if(!$consent) {
            return array(null,null,null);
        }
        return array_values($consent);
    }

}
