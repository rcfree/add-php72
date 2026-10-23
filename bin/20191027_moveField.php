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

moveFields("copd","demogadm","lifestyle",array("alcohol_consumption","hc1", "height","weight","arrow","bmi"));

function moveFields($modName, $sourcePageName, $targetPageName, $fieldNames) {
    foreach($fieldNames as $fieldName) {
        moveField($modName,$sourcePageName,$targetPageName,$fieldName);
    }
}
function moveField($modName, $sourcePageName,$targetPageName,$fieldName) {

	$system = ADD::sys();
	$sourcePage = $system->getPageFromPim($modName.":".$sourcePageName);
	$targetPage = $system->getPageFromPim($modName.":".$targetPageName);


	$sourceEncType = $sourcePage->getSetting('encounter');
    if(!$targetPage) {
        $targetEncType = "copd-visit-lifestyle";
    }
    else {
        $targetEncType = $targetPage->getSetting('encounter');
    }
	$edb = ADD::db('encounter');
	$edb->beginTxn();
	$sourceEncForm = $edb->getEncounterFormWithType($sourceEncType);
	$targetEncForm = $edb->getEncounterFormWithType($targetEncType);

	if (!$targetEncForm) {
	    $targetEncForm = createEncounterForm($targetEncType);
    }
	
	$log = ADD::log("moveField.php");
	$sourceObsFields = $edb->getObservationFieldsByIdAndName($sourceEncForm);
	$targetObsFields = $edb->getObservationFieldsByIdAndName($targetEncForm);

	$sourceObsField = ADD_Util::val($fieldName,$sourceObsFields);
    $targetObsField = ADD_Util::val($fieldName,$targetObsFields);
    if($targetObsField && $sourceObsField) {
        throw new Exception("Field '$fieldName' is present in both source and target");
    }
    if(!$sourceObsField && !$targetObsField) {
        $log->info("Field '$fieldName' not found in source ".$sourceEncType. " or target ".$targetEncType);
    }
    if($targetObsField) {
        $log->warn("Field '$fieldName' already moved to target ".$targetEncType);
    }
    else {
        $edb->update("observation_field",$sourceObsField,array("id"=>$sourceObsField['id'],"encounter_form_id"=>$targetEncForm['id'],"level"=>1));
    }


    $obsField = $edb->fetchFirst("select * from observation_field where name=? and encounter_form_id=?", array($fieldName, $targetEncForm['id']));

	$sourceObsList = $edb->fetchAll("select o.*,e1.id as source_id, e1.patient_id, e1.parent_encounter_id, e2.id as target_id, ef1.type as source_type, ef2.type as target_type from observation o left join encounter e1 on o.encounter_id = e1.id".
        " left join encounter e2 on e1.parent_encounter_id=e2.parent_encounter_id and e2.encounter_form_id = ?".
        " left join encounter_form ef1 on e1.encounter_form_id = ef1.id".
        " left join encounter_form ef2 on e2.encounter_form_id = ef2.id".
        " where o.observation_field_id=?", array($targetEncForm['id'],$obsField['id']));

	#$sourceEncs = $edb->fetchAll("select source.patient_id, source.id as source_id, target.id as target_id, source.parent_encounter_id from encounter source
	#left join encounter target on target.parent_encounter_id = source.parent_encounter_id and target.encounter_form_id=?
	#where source.encounter_form_id=?",array($targetEncForm['id'], $sourceEncForm['id']));

    $created = 0;
    $previous = 0;
    $updated = 0;
    foreach($sourceObsList as $sourceObs) {
        if(ADD_Util::isEmpty($sourceObs['target_id'])) {
            #$log->warn("CREATE new target enc as one does not exist for source '".$sourceObs['source_id']."' field '$fieldName'");
            $enc = $edb->insert("encounter",array("patient_id"=>$sourceObs['patient_id'],"parent_encounter_id"=>$sourceObs['parent_encounter_id'],"encounter_form_id"=>$targetEncForm['id'],"is_deleted"=>"0"),array("date_created","date_updated"));
            $sourceObs['target_id']=$enc['id'];
            $created++;
        }

        if ($sourceObs['source_id']==$sourceObs['target_id']) {
            #$log->info("Observation for field '$fieldName' has already been moved to ".$targetEncType);
            $previous++;
            continue;
        }
        $edb->execute("update observation set encounter_id=? where observation_field_id=? and encounter_id=?",array($sourceObs['target_id'],$obsField['id'],$sourceObs['source_id']));
        #$log->info("Update observation set '".$sourceObs['source_id']." to '".$sourceObs['target_id']."' field '$fieldName'");
        $updated++;
    }
    $log->info("**Moved $fieldName from $sourceEncType to $targetEncType");
    $log->info(count($sourceObsList)." total observations | $created encounters created | $previous already present | $updated moved");
	$edb->commitTxn();
}

function createEncounterForm($encType) {
    if (strlen($encType) > 128)
        throw new ADD_EncounterException("Encounter type is too long (>128 chars)");

    $efData = array(
        "type" => $encType
    );
    $efData['revision_no'] = 1;
    $encForm = CLE::db('encounter')->insert("encounter_form", $efData,array("date_created","date_updated"));


    CLE::db('audit')->auditEncounterForm('create', $encForm);
    return $encForm;
}