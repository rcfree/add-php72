<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EncounterHelper
 *
 * @author rcf8
 */
class ADD_Database_Util_EncounterHelper {
	function populateObsData($obsData, $newValue) {
        $obsValue = null;

        if (is_array($newValue)) {
            $obsValue = ADD_Util::serialize($newValue);
            $obsData['is_serialized'] = 1;
        } else {
            $obsValue = $newValue;
            $obsData['is_serialized'] = 0;
        }

        if (strlen($obsValue) > 200) {
            $obsData['value'] = "";
			$obsData['note']=$obsValue;
        } else {
            $obsData['value'] = $obsValue;
			$obsData['note']=null;
        }

        return $obsData;
    }
	
	function determineObservations($encId, $obsFields, $obs, $newValues) {
		$insertedObs = array();
		$updatedObs = array();
		
		$storeField = array();
		$oldObs = array();
		
        foreach ($obs as $ob) {
            $of = ADD_Util::val($ob['observation_field_id'], $obsFields);
            if ($of['is_deleted'] == 1)
                continue;
            if (!$of)
                throw new ADD_DatabaseException("Obs field with ID '" . $ob['observation_field_id'] . "' missing");
            $oldObs[$of['name']] = $ob;
        }
		$auditInserted = array();
		$auditUpdated = array();
		foreach ($newValues as $fieldName => $newValue) {
            if (isset($storeField[$fieldName]))
                throw new ADD_DatabaseException("Multiple '" . $name . "' observations found in this encounter", true);
            
			$of = ADD_Util::val($fieldName, $obsFields);
            if (!$of)
                throw new ADD_DatabaseException("Observation field '$fieldName' is not defined in encounter type");
			
			if ($of['is_deleted'] == 1)
                continue;
            
			$obs = ADD_Util::val($fieldName, $oldObs);
			
            if (!$obs) {
                #new observation of this type for this encounter
                $obsData = array("encounter_id" => $encId, "observation_field_id" => $of["id"]);
				$insertedObs[$fieldName]=$this->populateObsData($obsData, $newValue);
                
            } else {
				
                $oldValue = $obs['value'] . $obs['note'];
                if ($obs['is_serialized']==1) $oldValue=ADD_Util::unserialize($oldValue);
				
				if ($newValue != $oldValue) {
                    $updatedObs[$fieldName] = $this->populateObsData($obs,$newValue);
                }
				
            }
			$storeField[$fieldName] = true;
        }
		
		return array($insertedObs,$updatedObs);	
	}
	
	function canResolveDuplicateObs($obs) {
		$existingObsValues = array();
		$duplicateObs = array();
		foreach($obs as $ob) {
			$ofId = $ob['observation_field_id'];
			if(isset($existingObsValues[$ofId])) {
				if($existingObsValues[$ofId]!=$ob['value']) {
					return array(false,array());
				}
				$duplicateObs[] = $ob;
			}
			$existingObsValues[$ofId]=$ob['value'];
		}
		return array(true,$duplicateObs);
	}
}
