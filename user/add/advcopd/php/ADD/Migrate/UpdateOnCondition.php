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
class ADD_Migrate_UpdateOnCondition {
    private $_migSection = null;
    private $_modName = null;
    function __construct($modName, $migSection) {
        $this->_modName = $modName;
        $this->_migSection = $migSection;
    }
    function migrate($newConfig) {
            $log = ADD::log();
            $edb = ADD::db('encounter');
            $adb = ADD::db('audit');
            $migSection = $this->_migSection;
            $newSection = $newConfig->getSection($migSection->getSectionName());
            $encType = $newSection->getSetting('encounter');
            
            $log->info("Migrate update on condition for '$encType' when ".$migSection->getSetting('test_field')." is ".$migSection->getSetting('test_op'));
            
            $encsToUpdate = $edb->fetchAll("select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=?",$encType);
            $testField = $migSection->assertSetting('test_field');
            $testOp = $migSection->assertSetting('test_op');
            $updateVals = $migSection->assertSetting('update_value');
            foreach($updateVals as $name => $updateVal) {
                $updateVals[$name] = substr($updateVal,0,1)=="{" ? json_decode($updateVal,true) : $updateVal;
            }
			$log->info("Potentially ".count($encsToUpdate)." encounters to update");
               
            foreach($encsToUpdate as $encToUpdate) {
            	$obs = $edb->getObservationsInEncounter($encToUpdate);
				$testVal = ADD_Util::val($testField,$obs);
                if ($testOp=="not_empty"  && !ADD_Util::isEmpty($testVal)) {
                    list($createdObs, $updatedObs) = $edb->updateEncounterObservations($encToUpdate,$updateVals);
                    $log->info("Migrate enc:".$encToUpdate['id']." where $testField = $testVal => ".json_encode($updateVals));
                    $adb->auditEncounter("migrate",$encToUpdate,$createdObs,$updatedObs);
                }
                
            }
            
}
}
