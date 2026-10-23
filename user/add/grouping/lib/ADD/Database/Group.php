<?php


class ADD_Database_Group extends ADD_Database {
	function createGroup($name,$desc) {
		return $this->insert("`group`",array("name"=>$name,"description"=>$desc),array("date_created"));
	}
	function doesGroupExist($name) {
		return $this->fetchSingleValue("select count(*) from `group` where name=?",array($name)) > 0 ? true : false;
	}
	function addPatientToGroup($group,$patient) {
		$this->insert("patient_group",array("group_id"=>$group['id'],"patient_id"=>$patient['id']));
	}
	function getAllGroups() {
		return $this->fetchAll("select * from `group`");
	}
	function getGroup($groupId) {
		return $this->fetchFirst("select * from `group` where id=?",array($groupId));
	}
	function getPatientsInGroup($groupId) {
		return $this->fetchAll("select p.*,
		(select di.title from observation o 
	left join observation_field of on o.observation_field_id=of.id
	left join dictionary_item di on o.value = di.value and di.dictionary_id=84
	where o.encounter_id=res_contact_detail.id and of.name='current_status'
) as 'current_status',
(select o.value from observation o 
	left join observation_field of on o.observation_field_id=of.id
	where o.encounter_id=res_dbrequest.id and of.name='pat_sent_db_consent'
) as 'pat_sent_db_consent',
(select o.value from observation o 
	left join observation_field of on o.observation_field_id=of.id
	where o.encounter_id=res_dbrequest.id and of.name='date_sent'
) as 'date_sent'
		 from patient p
		left join patient_group pg on pg.patient_id = p.id 
		left join encounter as res on res.patient_id = p.id and res.encounter_form_id=117
		left join encounter as res_contact_detail on p.id = res_contact_detail.patient_id and res_contact_detail.encounter_form_id=139 and res_contact_detail.is_deleted != 1
		left join encounter as res_dbrequest on p.id = res_dbrequest.patient_id and res_dbrequest.encounter_form_id=131 and res_dbrequest.is_deleted != 1
		where pg.group_id=?",array($groupId));
	}
}
?>
