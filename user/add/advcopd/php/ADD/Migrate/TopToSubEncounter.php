<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TopToSubEncounter
 *
 * @author rcf8
 */
class ADD_Migrate_TopToSubEncounter {
    private $_migSection = null;
    private $_modName = null;
    function __construct($modName, $migSection) {
        $this->_modName = $modName;
        $this->_migSection = $migSection;
    }
    function migrate($newConfig) {
            $log = ADD::log();
            $edb = ADD::db('encounter');
            $migSection = $this->_migSection;
            $newParent = $migSection->getSetting('newParent');
            $newSection = $newConfig->getSection($migSection->getSectionName());
            $parentSection = $newConfig->getSection($newParent);
            
            $log->info("Migrate parent has changed from null to '$newParent'");
            
            $encType = $newSection->getSetting('encounter');
            $parentEncType = $parentSection->getSetting('encounter');
            $parentEncForm = $edb->getEncounterFormWithType($parentEncType);
            if (!$parentEncForm) throw new Exception("Encounter form '$parentEncType' does not exist.");
            $encsToUpdate = $edb->fetchAll("select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=?",$encType);
           
            foreach($encsToUpdate as $encToUpdate) {
                $encId = $encToUpdate['id'];
                $parentEnc = $edb->getEncounter($encToUpdate['parent_encounter_id']);
                if ($parentEnc['encounter_form_id']==$parentEncForm['id']) {
                    $log->info("Parent encounter type is already correct for encounter $encId");
                    continue;
                }
                $log->info("Parent encounter type is NOT correct for encounter $encId"); 
                if ($encToUpdate['parent_encounter_id']==null) {
                    $log->info("Encounter '$encId' does not have a parent");  
                    
                    $patientId = $encToUpdate['patient_id'];
                    $topEncSection = $newConfig->getSection($this->_modName);
                    $topEncType = $topEncSection->getSetting('encounter');
                    $topEncForm = $edb->getEncounterFormWithType($topEncType);
                    
                    $topEncForPatient = $edb->fetchFirst("select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=? and e.patient_id=?",array($topEncType,$patientId));
                    $changeData = array();
                    if (!$topEncForPatient) {
                        $topEncForPatient = $edb->insert("encounter",array("encounter_form_id"=>$topEncForm['id'],"patient_id"=>$patientId,"revision_no"=>1,"parent_encounter_id"=>null));
                        $log->info("Inserted top encounter ".json_encode($topEncForPatient));
                    }
                    if ($topEncType==$parentEncType) {
                        $changeData = array("parent_encounter_id"=>$topEncForPatient['id']);
                    }
                    else {
                        $pencForPatient = $edb->fetchFirst("select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where e.parent_encounter_id=? and e.encounter_form_id=? and e.patient_id=?",array($topEncForPatient['id'],$parentEncForm['id'],$patientId));
                        if (!$pencForPatient) {
                         $pencForPatient = $edb->insert("encounter",array("parent_encounter_id"=>$topEncForPatient['id'],"encounter_form_id"=>$parentEncForm['id'],"patient_id"=>$patientId,"revision_no"=>1));
                         $log->info("Inserted parent encounter ".json_encode($pencForPatient));
                        }
                        else {
                         $log->info("Parent encounter exists already: ".json_encode($pencForPatient));
                        }
                        $changeData = array("parent_encounter_id"=>$pencForPatient['id']);
                    }
                    $log->info("Updated encounter ".$encToUpdate['id']." with parent encounter:".json_encode($changeData));
                    $edb->update("encounter",$encToUpdate,$changeData);    
                }
                
            }
}
}
