<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$home = getenv('ADD_PATH');
include_once($home."/vendor/autoload.php");
 
use ClinicLE\System\Environment\StandardEnvironment;
use ClinicLE\System\Initialiser;


$env = new StandardEnvironment($home,getenv('ADD_PROFILE'));
$init = new Initialiser($env);

$system = ADD::init($init);
$system->initServiceContainer();
$system->initModules();
moveFields("pr","iswt","exercise",array("iswt_bp_sys_rest_pre","iswt_bp_sys_rest_post","iswt_bp_dia_rest_pre","iswt_bp_dia_rest_post"));
moveFields("pr","eswt","exercise",array("eswt_bp_sys_rest_pre","eswt_bp_sys_rest_post","eswt_bp_dia_rest_pre","eswt_bp_dia_rest_post"));

function moveFields($modName, $sourcePageName,$targetPageName,$fieldNames) {
	$system = ADD::sys();
	$sourcePage = $system->getPageFromPim($modName.":".$sourcePageName);
	$targetPage = $system->getPageFromPim($modName.":".$targetPageName);
	
	$sourceEncType = $sourcePage->getSetting('encounter');
	$targetEncType = $targetPage->getSetting('encounter');
	
	$edb = ADD::db('encounter');
	$edb->beginTxn();
	$sourceEncForm = $edb->getEncounterFormWithType($sourceEncType);
	$targetEncForm = $edb->getEncounterFormWithType($targetEncType);
	
	$log = ADD::log("moveField.php");
	$sourceObsFields = $edb->getObservationFieldsByIdAndName($sourceEncForm);
	$targetObsFields = $edb->getObservationFieldsByIdAndName($targetEncForm);
	foreach($fieldNames as $fieldName) {
		$sourceObsField = ADD_Util::val($fieldName,$sourceObsFields);
		$targetObsField = ADD_Util::val($fieldName,$targetObsFields);
		if($targetObsField && $sourceObsField) {
			throw new Exception("Field '$fieldName' is present in both source and target");
		}
		if($targetObsField) {
			$log->warn("Field '$fieldName' already moved to target ".$targetEncForm['type']);
			continue;
		}
		if(!$sourceObsField) {
			$log->info("Field '$fieldName' not found in source ".$sourceEncForm['type']);
			continue;
		}
		$edb->update("observation_field",$sourceObsField,array("encounter_form_id"=>$targetEncForm['id']));
	}
	$sourceEncs = $edb->fetchAll("select source.patient_id, source.id as source_id, target.id as target_id, source.parent_encounter_id from encounter source 
	left join encounter target on target.parent_encounter_id = source.parent_encounter_id and target.encounter_form_id=?
	where source.encounter_form_id=?",array($targetEncForm['id'], $sourceEncForm['id']));
	foreach($fieldNames as $fieldName) {
		$obsField = ADD_Util::val($fieldName,$sourceObsFields);
		if(!$obsField) {
			$obsField = ADD_Util::val($fieldName,$targetObsFields);
		}
		$counter = 0;
		foreach($sourceEncs as $sourceEnc) {
			if(ADD_Util::isEmpty($sourceEnc['target_id'])) {
				$log->warn("CREATE new target enc as one does not exist for source '".$sourceEnc['source_id']."' field '$fieldName'");
				$enc = $edb->insert("encounter",array("patient_id"=>$sourceEnc['patient_id'],"parent_encounter_id"=>$sourceEnc['parent_encounter_id'],"encounter_form_id"=>$targetEncForm['id'],"is_deleted"=>"0"),array("date_created","date_updated"));
				$sourceEnc['target_id']=$enc['id'];
				$sourceEncs[$counter]=$sourceEnc;
			}
				
			$edb->execute("update observation set encounter_id=? where observation_field_id=? and encounter_id=?",array($sourceEnc['target_id'],$obsField['id'],$sourceEnc['source_id']));
			$log->info("Update observation set '".$sourceEnc['source_id']." to '".$sourceEnc['target_id']."' field '$fieldName'");
			$counter++;
		}
	}
	$edb->commitTxn();
}
