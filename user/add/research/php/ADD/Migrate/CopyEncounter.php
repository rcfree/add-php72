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
class ADD_Migrate_CopyEncounter {
    private $_migSection = null;
    private $_modName = null;
    function __construct($modName, $migSection) {
        $this->_modName = $modName;
        $this->_migSection = $migSection;
    }
	
	function getFieldMappings($includeFieldList) {
		$fieldMap = array();
		foreach($includeFieldList as $includeField) {
			$temp = explode(":",$includeField);
			$srcField = $temp[0];
			$tgtField = count($temp)==2 ? $temp[1] : $temp[0];
			$srcIncludeFields[]=$srcField;
			$tgtIncludeFields[]=$tgtField;
			
			$fieldMap[$srcField]=$tgtField;
		}
		return array($srcIncludeFields,$tgtIncludeFields,$fieldMap);
	}
	
    function migrate($newConfig) {
            $log = ADD::log();
            $edb = ADD::db('encounter');
            $migSection = $this->_migSection;
            $encType="res-general-consent";
            $encForm = $edb->getEncounterFormWithType($encType);
            if (!$encForm) throw new Exception("Encounter form '$encType' does not exist.");
			
            $patientsToCopy = $edb->fetchAll("select e.patient_id from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=?",$encType);
			
			list($srcIncludeFields,$tgtIncludeFields, $fieldMap) = $this->getFieldMappings($migSection->getSettingList("includeFields",","));
			
			$topPage = ADD::sys()->getModule($this->_modName)->getPage($this->_modName);
			$em = ADD::sys()->getEncounterManager();
			foreach($patientsToCopy as $patient) {
				$ter = $em->record("res",$patient['patient_id']);
				$er = $em->child($ter,"res-general");
				if(!$er->exists()) continue;
				$consent = $em->child($er,"res-general-consent");
				if (!$consent->exists()) continue;
				$consent->setIncludeDeletedObs(true);
				$consent->retrieve();
				$obsValues = $consent->getObsValues($srcIncludeFields);
				
				$contact = $em->child($ter,"res-contact");
				if(!$contact->exists()) $contact->store();
				$contact->retrieve();
				
				$details = $em->child($contact,"res-contact-details");
				$mappedObsValues = $this->mapObsValues($obsValues,$fieldMap);
				
				if($details->exists()) {
					$details->setIncludeDeletedObs(true);
					$details->retrieve();
					$existingObsValues = $details->getObsValues($tgtIncludeFields);
					if($existingObsValues==$mappedObsValues) {
						$log->warn("Contact details for patient:".$patient['patient_id']." already exists - same values:".json_encode($existingObsValues));
						continue;
					}
					else {
						$log->warn("Contact details for patient:".$patient['patient_id']." already exists - different values");
						$log->warn("existing values:".print_r($existingObsValues,1)." -> replacing with:".print_r($mappedObsValues,1));
						$log->warn("Replace existing values Y/n?");
						$value = readline();
						if(strtoupper($value)!="Y") continue;
					}
				}
				
				$details->store($mappedObsValues);
				$log->info("Added new details for patient:".$patient['patient_id'])." = ".json_encode($mappedObsValues);
            }
      }
	function mapObsValues($obsValues,$fieldMap) {
		$tgtObsValues = array();	
		foreach($obsValues as $srcObsName => $obsValue) {
			$tgtObsName = $fieldMap[$srcObsName];
			$tgtObsValues[$tgtObsName]=$obsValue;
		}
		return $tgtObsValues;
	}
			
}
?>