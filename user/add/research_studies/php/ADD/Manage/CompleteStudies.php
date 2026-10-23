<?php

/**
 * Description of Load
 *
 * @author rcf8
 */

class ADD_Manage_CompleteStudies implements ADD_ManageInterface {

	function getOptsList() {
		return "";
	}

	function getDescription() {
		return "Complete studies";
	}

	function manage($options) {
		$add = ADD::sys();
		$add->initModules();
		$log = ADD::log($this);
		$ddb = ADD::db('dictionary');
		$studies = $ddb -> getDictionaryItems("studylist");

		$completedStudies = array();
		$compareDt = new DateTime();
		foreach ($studies as $study) {
			$studyData = ADD_Util::unserialize($study['data']);
			$studyValue = $study['value'];
			$studyEndDate = ADD_Util::val('study_end_date', $studyData);
			if (!ADD_Util::val('study_end_date', $studyData))
				continue;
			$studyEndDt = DateTime::createFromFormat("d/m/Y", $studyEndDate);
			if($studyEndDt>$compareDt) {
				continue;
			}
			$log->info("Study ".$study['title']."(no ".$study['value']."), end: ".$studyEndDt->format("d/m/Y")." <= today:".$compareDt->format("d/m/Y"));
				
			$completedStudies[$studyValue] = $studyEndDt;
		}

		$patientsEnrolledSql = "select 
study.id as study_id,
study_detail.id as study_detail_id,
study.patient_id,
(select o.value from observation o 
	left join observation_field of on o.observation_field_id=of.id
	where o.encounter_id=study_detail.id and of.name='study'
) as study_no,
(select o.value from observation o 
	left join observation_field of on o.observation_field_id=of.id
	where o.encounter_id=study_detail.id and of.name='current_status'
) as current_status
from encounter study
left join encounter study_detail on study.id = study_detail.parent_encounter_id
left join encounter_form study_form on study.encounter_form_id = study_form.id
left join encounter_form study_detail_form on study_detail.encounter_form_id = study_detail_form.id
where study.is_deleted!=1 and study_form.type = 'res-study' and study_detail_form.type = 'res-study-details' 
and
(select o.value from observation o 
	left join observation_field of on o.observation_field_id=of.id
	where o.encounter_id=study_detail.id and of.name='study'
)=?";

		$edb = ADD::db('encounter');
		$completedPatients = array();
		
		$em = $add->getEncounterManager();
		$actionPage = $add->getPageFromPim("research:actions");
		$actionPage->setupComponents();
		$actionPage->setPermission('edit',true);
		
		$studyPage = $actionPage->getParent();
		
		$detailsPage = $studyPage->getChildPage('sdetails');
		$detailsPage->setupComponents();
		$detailsPage->setPermission('edit',true);
		
		$researchPage = $studyPage->getParent();
		
		ADD::db()->beginTxn();
		$user = new stdClass;
		$user->username="autoupdate";
		
		$add->getAccessManager()->setUser($user);
		foreach ($completedStudies as $studyValue => $endDt) {
			
			
			$rows = $edb -> fetchAll($patientsEnrolledSql, array($studyValue));
			
			foreach($rows as $row) {	
				if($row['current_status']!=3) continue;
				$add->setPatientId($row['patient_id']);
				$add->setParam('eid',$row['study_id']);
				
				$submitValues = array(
					"__pid"=>$row['patient_id'],
					"current_status"=>8,
					"__token"=>ADD_Util::token()
				);
				$detailsPage->submitForm($submitValues);
				ADD::web()->afterSubmit($detailsPage);
				$actionPage->getComponent('Encounter')->reset();
				$studyPage->getComponent('Encounter')->reset();
				$researchPage->getComponent('Encounter')->reset();
				$log->info("Complete study for patient ".$row['patient_id']." and study ".$studyValue.". Study end date ".$studyEndDt->format("d/m/Y"));
				
			}

		}
		ADD::db()->commitTxn();		
	}

	function runAtEnd($exception = null) {

	}

}
?>
