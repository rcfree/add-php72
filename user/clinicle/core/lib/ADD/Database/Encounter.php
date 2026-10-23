<?php

class ADD_Database_Encounter extends ADD_Database {
	function __construct() {
		$this->_encounterHelper=new ADD_Database_Util_EncounterHelper();
	}
	function setEncounterHelper(ADD_Database_Util_EncounterHelper $encounterHelper) {
		$this->_encounterHelper=$encounterHelper;
	}
    function getAllEncounterForms() {
        return EncounterFormQuery::create()->find();
    }

    function getEncounterForm($id) {
        return $this->getFromCacheFunction("selectById", array("encounter_form", $id));
    }

    function purgeFormAndFields($encForm) {
        $obsFields = $this->getObservationFieldsInEncounterForm($encForm['id'],false);
		
        foreach ($obsFields as $obsField) {
			$this->execute("delete from observation_field_audit where observation_field_id=?",$obsField['id']);
        }
		$this->execute("delete from observation_field where encounter_form_id=?",$encForm['id']);
		
        ADD::db('audit')->createAudit(array("record_type"=>"form","action"=>"purge","record_id"=>null,"record_name"=>$encForm['type'],"user_id"=>ADD::db('audit')->currentUser()));
		$this->execute("delete from encounter_form where id=?",$encForm['id']);	
    }

    function updateEncounter($encData) {
        if (!isset($encData['id']))
            throw new ADD_DatabaseException("Cannot update encounter - no ID provided");
        $this->update("encounter", $encData['id'], $encData, array("date_updated"));
    }

    function createEncounter($encData = null) {
        if (!isset($encData['patient_id'])) {
            throw new ADD_DatabaseException("No patient ID provided for '" . $encData['name']);
        }

        if (empty($encData['encounter_type']))
            throw new ADD_DatabaseException("Cannot create encounter with empty 'type'");
        $encForm = $this->getEncounterFormWithType($encData['encounter_type']);
        if (!$encForm)
            throw new ADD_DatabaseException("The Encounter type '{$encData['encounter_type']}' is not defined");

        $encData['encounter_form_id'] = $encForm['id'];
        $enc = $this->insert("encounter", $encData, array("date_created", "date_updated"));
        return $enc;
    }

    function getEncounterFormWithType($type) {
        return $this->getFromCacheFunction("selectFirstByField", array("encounter_form", "type", $type));
    }

    function getFromCacheFunction($function, $params) {
    	return call_user_func_array(array($this, $function), $params);
		
    }
/*
    function getEncounterForm($id) {
        return $this->getFromCacheFunction("selectById", array("encounter_form", $id));
    }

 * 
 */
    function purgeObsField($obsField) {
        $ofId = is_array($obsField) ? $obsField['id'] : $obsField;
        $this->execute("delete from observation where observation_field_id=?", $ofId);
        $this->execute("delete from observation_field_audit where observation_field_id=?", $ofId);
        $this->execute("delete from observation_field where id=?", $ofId);
    }

    function deleteObsField($obsField) {
        $this->update("observation_field", $obsField, array("is_deleted" => 1));
    }

    function purgePatientEncounters($patient) {
        $patientId = is_array($patient) ? $patient['id'] : $patient;
        $add = ADD::sys();
        $encs = $this->fetchAll("select * from encounter e where parent_encounter_id is null and patient_id=?", $patientId);

        $rel = $this->purgeRelatedEncounters($encs);
        $encs = $this->fetchAll("select * from encounter e where patient_id=?", $patientId);

        $this->purgeRelatedEncounters($encs);
    }

    function deletePatientEncounters($patient) {
        $patientId = is_array($patient) ? $patient['id'] : $patient;
        $add = ADD::sys();
        $encs = $this->fetchAll("select * from encounter e where parent_encounter_id is null and patient_id=?", $patientId);

        $rel = $this->deleteRelatedEncounters($encs);
        $encs = $this->fetchAll("select * from encounter e where patient_id=?", $patientId);

        $this->deleteRelatedEncounters($encs);
        return $rel;
    }

    function purgeRelatedEncounters($encs) {
        if(count($encs)==0) return;
       
		$encIds = array();
		foreach ($encs as $enc) {
			$cencs = $this->selectAllByField("encounter", "parent_encounter_id", $enc['id']);
			$this->purgeRelatedEncounters($cencs);
            
            //$msgs[] = 
			$this->purgeObsInEnc($enc);
                //$cids[] = $cenc['id'];
            //}
			$encIds[]=$enc['id'];
		}
        if (count($encIds) > 0) {
                $this->execute("delete from encounter where id in (" . implode(",", $encIds) . ")");
        }

    }

    function deleteRelatedEncounters($encs) {
        $msgs = array();
        $encIds = array();
        foreach ($encs as $enc) {
            $encIds[] = $enc['id'];
        }
        if(count($encIds)==0) {
            return $msgs;
        }
        $params = array_merge(array($this->now()), $encIds);
        $this->execute("update encounter set is_deleted=1, date_updated=? where id in (" . $this->makePlaceholders($encIds) . ")", $params);
        return $msgs;
    }

    public function purgeObsInEnc($enc) {
        $obs = $this->selectAllByField("observation", "encounter_id", $enc['id']);
        foreach ($obs as $ob) {
            $this->deleteByField("observation_audit", "observation_id", $ob['id']);
        }


        if ($obs)
            $this->deleteByField("observation", "encounter_id", $enc['id']);
        $name = $this->fetchSingleValue("select `type` from encounter_form where id=?", array($enc['encounter_form_id']));
        return array($name, $enc);
    }

    public function deleteObsInEnc($enc) {
        $this->execute("update observation set is_deleted=1, date_updated=? where encounter=?", array($this->now(), $enc['id']));
        return array($name, $enc);
    }

    function _getObservations($encId) {
        return $this->selectAllByField("observation", "encounter_id", $encId);
    }

    function getObservationsInEncounter($encounter, $includeDeleted=false) {
        $obsFields = $this->getObservationFieldsByIdAndName($encounter['encounter_form_id']);

        $obsList = $this->_getObservations($encounter['id']);

        $outList = array();

        foreach ($obsList as $obs) {
            $obsField = ADD_Util::val($obs['observation_field_id'], $obsFields);
            if (!$obsField)
                throw new ADD_DatabaseException("Field with ID " . $obs['observation_field_id'] . " was not found in encounter:" . $encounter['id'] . ", obs:<pre>" . print_r($obs, 1) . "</pre>");
 			if (!$includeDeleted && $obsField['is_deleted'] == 1)
                continue;
            $name = $obsField['name'];
            if (isset($outList[$name])) {
				return $this->_getResolvedDuplicateObs($encounter,$obsList,$includeDeleted);
			}
            if (strlen($obs['note'])>0) {
                $obs['value'] = $obs['note'];
            }
            $outList[$name] = $obs['is_serialized'] == 1 ? ADD_Util::unserialize($obs['value']) : $obs['value'];
        }
        return $outList;
    }

	function _getResolvedDuplicateObs($encounter,$obs,$includeDeleted) {
		list($isResolved,$dupObs) = $this->_encounterHelper->canResolveDuplicateObs($obs);
		if(!$isResolved) {
			$dupList = array();
			$obsFields = $this->getObservationFieldsByIdAndName($encForm['id']);

			foreach($obs as $ob) {
				$obsField = ADD_Util::val($ob['observation_field_id'], $obsFields); 
				$dupList[]=$obsField['name']."=".$ob['value'];
			}
			throw new ADD_DatabaseException("Multiple observations found in this encounter which could not be resolved (id:".$encounter['id'].")<pre>".print_r($dupList,1)."</pre>", true);
		}
		foreach($dupObs as $dupOb) {
			$this->execute("delete from observation_audit where observation_id=?",$dupOb['id']);
			$this->execute("delete from observation where id=?",$dupOb['id']);
		}
		ADD::web()->addWarning("Duplicate observations were found in a data record. They have been resolved");
		return $this->getObservationsInEncounter($encounter,$includeDeleted);
	}
	
    function countObservationsWithValue($obsName, $obsValue, $encType, $ignoreEnc = null) {
        $encForm = $this->getEncounterFormWithType($encType);

        $sql = "select count(*)
            from observation o left join observation_field of on o.observation_field_id = of.id left join encounter e on o.encounter_id = e.id
            where o.value=? and of.name=? and e.encounter_form_id=?";
        $params = array($obsValue, $obsName, $encForm['id']);
        if ($ignoreEnc) {
            $sql.=" and e.id != ?";
            $params[] = $ignoreEnc;
        }
        return $this->fetchSingleValue($sql, $params);
    }

    function getObservationsUsingField($obsField, $start=0,$rowCount=100) {
        $ofId = is_array($obsField) ? $obsField['id'] : $obsField;
        $obs = $this->fetchAll("select o.id, coalesce(o.value,o.note) as `value`,o.is_serialized,o.is_deleted,o
        .encounter_id from (select id from observation
        where observation_field_id=? order by id limit $start,$rowCount) q
        join observation o 
        on o.id = q.id",
            $ofId);

        $proObs = array();
        foreach ($obs as $ob) {
           if ($ob['is_serialized'])
                $ob['value'] = ADD_Util::unserialize($ob['value']);
			$proObs[]=$ob;
        }
		
        return $proObs;
    }
	
	function getEncountersWithObservationValue($encFormName, $obsFieldName,$obsValue) {
		$params = array($encFormName,$obsFieldName,$obsValue);
		return $this->fetchAll("select e.* from encounter e "
			. "left join observation o on o.encounter_id = e.id "
			. "left join encounter_form ef on e.encounter_form_id = ef.id "
			. "left join observation_field of on of.id = o.observation_field_id "
			. "where ef.type=? and of.name=? and o.value=?",
			$params
		);
	}

    function getEncounter($id) {
        return $this->selectById("encounter", $id);
    }

	function getSection($id) {
        return $this->selectById("section", $id);
    }
	function getSectionTypeWithName($name) {
		return $this->fetchFirst("select * from section_type where name=?",$name);
	}
    function getEncountersWithPatientAndType($pid, $encType) {
        return $this->fetchAll(
                        "select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=? and e.patient_id=? and e.is_deleted=0", array($encType, $pid)
        );
    }
	 function getEncountersWithType($encType) {
        return $this->fetchAll(
                        "select e.* from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where ef.type=? and e.is_deleted=0", array($encType)
        );
    }

    function createChildEncounterWithTypeAndObservations($penc, $encForm, $obsList, $badge=null, $encData=array()) {
        if (!$penc)
            throw new ADD_DatabaseException("No parent encounter provided");
        $cef = $this->getEncounterFormWithType($encForm);
        if (!$cef)
            throw new ADD_DatabaseException("The encounter type '$encForm' is not defined");
        //if (!$isLocked)
         //   $isLocked = "1";
        $cenc = array(
            "patient_id" => $penc['patient_id'],
            "encounter_form_id" => $cef['id'],
            "parent_encounter_id" => $penc['id'],
         //   "is_locked" => $isLocked,
            "revision_no" => 1,
			"badge"=>$badge,
        );
		$cenc = $cenc + $encData;
		if($obsList) {
		 	
        	$cenc["data_hash"]=ADD_Util::hashData($obsList);
        }
		try {
		    $cenc = $this->insert("encounter", $cenc, array("date_created", "date_updated"));
        } catch (PDOException $ex) {
            if (preg_match("/Integrity constraint violation.+parent_encounter_id/", $ex->getMessage()) || preg_match("/Incorrect integer value.+column 'parent_encounter_id'/", $ex->getMessage())) {
                throw new ADD_DatabaseException("Parent encounter with ID '{$penc['id']}' does not exist");
            }
            if (preg_match("/Integrity constraint violation.+patient_id/", $ex->getMessage()) || preg_match("/Incorrect integer value.+column 'patient_id'/", $ex->getMessage())) {
                throw new ADD_DatabaseException("Patient with ID '{$penc['patient_id']}' does not exist");
            }
            throw $ex;
        }
        list($cobs, $uobs) = $this->updateEncounterObservations($cenc, $obsList);
        return array($cenc, $cobs);
    }

    function createEncounterWithTypeAndObservations($patientId, $encForm, $obsList = array(),$additionalEncData=array()) {
        $et = $this->getEncounterFormWithType($encForm);
        if (!$et)
            throw new ADD_DatabaseException("The encounter type '$encForm' is not defined");
        $encData = array(
            'patient_id' => $patientId,
            'encounter_form_id' => $et['id'],
            'revision_no' => 1,
            
        );
		$encData = $encData + $additionalEncData;
		if($obsList) {
			$encData["data_hash"]=sha1(serialize($obsList));
		}
		
        try {
            $enc = $this->insert("encounter", $encData, array("date_created", "date_updated"));
        } catch (PDOException $ex) {
            if (preg_match("/Integrity constraint violation.+patient_id/", $ex->getMessage()) || preg_match("/Incorrect integer value.+column 'patient_id'/", $ex->getMessage())) {
                throw new ADD_DatabaseException("Patient with ID '$patientId' not found in the database");
            }
            throw $ex;
        }

        list($cobs, $uobs) = $this->updateEncounterObservations($enc, $obsList);
        return array($enc, $cobs);
    }

    function countEncountersWithPatientAndType($patientId, $encType) {

        if (!$patientId)
            throw new ADD_DatabaseException("A null 'patientId' is not allowed in countEncountersWithPatientAndType()");

        return $this->fetchSingleValue("select count(*) from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where e.patient_id=? and e.is_deleted=0 and ef.type=?", array($patientId, $encType)
        );
    }

    function countChildEncountersWithType($penc, $encType, $badge=null) {
        $encForm = $this->getEncounterFormWithType($encType);
		
		$sql = "select count(*) from encounter e where e.patient_id=? and e.is_deleted=0 and e.encounter_form_id=? and e.parent_encounter_id=?";
        $params = array($penc['patient_id'], $encForm['id'], $penc['id']);
        
		if($badge) {
			$sql.=" and e.badge=?";
			$params[]=$badge;
		}
		return $this->fetchSingleValue($sql, $params);
    }

    function getChildEncountersWithType($penc, $encType, $badge=null) {
        $sql = "select e.* from encounter e left join encounter_form ef on ef.id = e.encounter_form_id where e.patient_id=? and e.parent_encounter_id=? and ef.type=? and e.is_deleted=0";
		$params = array($penc['patient_id'], $penc['id'], $encType);
		if($badge) {
			$sql.=" and e.badge=?";
			$params[]=$badge;
		}
        return $this->fetchAll($sql,$params);
    }

    function getChildEncounterWithId($penc, $id) {
        return $this->fetchFirst(
			"select * from encounter e where e.patient_id=? and e.parent_encounter_id=? and e.id=? and e.is_deleted=0", array($penc['patient_id'], $penc['id'], $id)
        );
    }

    function getObservationFieldsByIdAndName($encForm, $notById = false) {
        return $this->getFromCacheFunction("_getObservationFieldsByIdAndName", array($encForm, $notById));
    }
	
    function _getObservationFieldsByIdAndName($encForm, $notById = false) {
        if (is_array($encForm)) {
        	$encForm = $encForm['id'];
		}
        $unsOfs = $this->getObservationFieldsInEncounterForm($encForm, false);

        $fieldList = array();

        foreach ($unsOfs as $of) {
            if (!$notById)
                $fieldList[$of['id']] = $of;
            $fieldList[$of['name']] = $of;
        }
        return $fieldList;
    }

    function unserializeFields($ofs) {
        $unsOf = array();
        foreach ($ofs as $of) {
            if(isset($of['settings'])) $of['settings'] = ADD_Util::unserialize($of['settings']);
           	if(isset($of['options'])) $of['options'] = ADD_Util::unserialize($of['options']);
            if(isset($of['concepts'])) $of['concepts'] = ADD_Util::unserialize($of['concepts']);
            $unsOf[] = $of;
        }
        return $unsOf;
    }

    function getObservationFieldsInEncounterForm($encForm, $excludeDeleted = true) {
        return $this->getFromCacheFunction("_getObservationFieldsInEncounterForm", array($encForm, $excludeDeleted));
    }

    function _getObservationFieldsInEncounterForm($encForm, $excludeDeleted = true) {
        if(is_numeric($encForm)) {	
        	$sql = "select *, options as options_raw from observation_field where encounter_form_id=?";
		}
		else {
			$sql = "select of.*, options as options_raw from observation_field of left join encounter_form ef on of.encounter_form_id = ef.id where ef.type=?";
		}
        if ($excludeDeleted)
            $sql.=" and is_deleted=0";
        $sql.=" order by weight";
        $ofs = $this->fetchAll($sql, $encForm);
        $data = $this->unserializeFields($ofs);
		return $data;
    }

     function insertObservation($obs) { //Data, $newValue) {
        return $this->insert("observation", $obs, array("date_created", "date_updated"));
    }

    function updateObservation($obs) { //Data, $newValue) {
        return $this->update("observation", $obs, null, "date_updated");
    }

    function updateEncounterObservations($enc, $newValues) {
        if (!$enc)
            throw new ADD_DatabaseException("Cannot update null encounter");
        $encId = is_array($enc) ? $enc['id'] : $enc;
        $enc = $this->getEncounter($encId);
        if (!$enc)
            throw new ADD_DatabaseException("Encounter record with ID '" . $encId . "' does not exist");
        if (count($newValues) == 0)
            return array(array(), array());
        
        $encFormId = ADD_Util::val("encounter_form_id", $enc);
        if (!$encFormId)
            throw new ADD_DatabaseException("Cannot update observations, the encounter " . $encId . " does not have an encounter type");
        
        $obsFields = $this->getObservationFieldsByIdAndName($enc['encounter_form_id']);

        $obs = $this->_getObservations($encId);

		list($insertedObs,$updatedObs) = $this->_encounterHelper->determineObservations($encId, $obsFields,$obs, $newValues);	
        
		$auditCreatedObs = array();
		$auditUpdatedObs = array();
		foreach($insertedObs as $obsName=>$obs) {
			$auditInsert = $this->insertObservation($obs);
			$auditCreatedObs[$obsName] = array("value"=>$auditInsert['value'],"note"=>$auditInsert['note']);
		}
		foreach($updatedObs as $obsName=>$obs) {
			$auditUpdate = $this->updateObservation($obs);
			$auditUpdatedObs[$obsName] = array("value"=>$auditUpdate['value'],"note"=>$auditUpdate['note']);
		}
        return array($auditCreatedObs, $auditUpdatedObs);
    }

    function getFieldFromEncounterForm($encType, $name) {
        $ofs = $this->unserializeFields(array($this->fetchFirst(
                    "select of.* from observation_field of left join encounter_form ef on of.encounter_form_id = ef.id where of.name=? and ef.type=?", array($name, $encType)
        )));
        return $ofs[0];
    }

    function incrementId($name) {
        $ir = $this->selectFirstByField("identifier", "name", $name);
        if (!$ir) {
            $irValue = 1;
            $ir = array("name" => $name, "value" => $irValue);
            $this->insert("identifier", $ir, array("date_created", "date_updated"));
        } else {
            $irValue = $ir['value'] + 1;
            $this->update("identifier", $ir, array("value" => $irValue), array("date_updated"));
        }
        return $irValue;
    }
	function updateObservationField($existing,$newData) {
		$obsField = $this->update("observation_field", $existing, $newData,"date_updated");
		ADD_Util::unserializeValues($obsField, array("settings", "options", "concepts"));
		return $obsField;
	}
	
	function insertObservationField($newData) {
		$obsField = $this->insert("observation_field", $newData,array("date_created","date_updated"));
		return $obsField;
	}
}
