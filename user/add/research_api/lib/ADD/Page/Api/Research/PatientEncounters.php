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
use Clinicle\Search\Query;
class ADD_Page_Api_Research_PatientEncounters extends ADD_Page_Api_Research {
	function apiOutput() {
		$page = ADD::sys()->getPageFromPim("resphy:detail");
		$form = $page->getComponent('Form');
		$form->setup();
		$query = new Query();
		$query->setPageNo(1);
		$query->setRowCount(50);
		//$query->setAliasPrefix(false);
		$query->addPatientJoin("resphy");
		$query->addEncounterJoin("resphy-exam","resphy");
		$query->addEncounterJoin("resphy-exam-detail","resphy-exam");
		$expectedFields = array("date_imported","height","source","match_state","pdf_filename");
		$query->addObservationFields("resphy-exam-detail",array("date_imported","height","source","match_state","pdf_filename"));
		$query->addEncounterFields("resphy-exam-detail",array("id","parent_encounter_id","date_created","patient_id"));
		$query->addEncounterFilter("resphy-exam-detail",array("id","is not",null));
		
		$data = $query->fetchData();
		$encounters = array();
		
		foreach($data as $row) {
			$factDate = $row['date_created'];
		
			$facts = array();
			foreach($form->getFields() as $field) {
				if(!$field->isStored()) {
					continue;
				}
				if(!in_array($field->getName(),$expectedFields)) {
					continue;
				}
				$facts[]=array("value"=>ADD_Util::val($field->getName(),$row),"timestamp"=>$factDate,"name"=>$field->getName(),"code"=>"resphy:detail:".$field->getName());
			}
			$encounter['id']=$row['id'];
			$encounter['patient_id']=$row['patient_id'];
			$encounter['parent_id']=$row['parent_encounter_id'];
			$encounter['timestamp']=$row['date_created'];
			$encounter['obs_facts']=$facts;
			$encounters[]=$encounter;
		}
		return $encounters;
	}
	
	function getExpectedPermissions() {
		return array('get');
	}
}
	