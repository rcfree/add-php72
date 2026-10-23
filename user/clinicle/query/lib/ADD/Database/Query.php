<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
;

class ADD_Database_Query extends ADD_Database {
	function fetchColumn($sql,$params=array(),$columnIndex=0) {
		$stmt = $this->execute($sql,$params);
		$rows = $stmt->fetchAll(PDO::FETCH_COLUMN,$columnIndex);
        return $rows;
	}	
	function fetchDataRows($fromClause, $idField, $idList, $additionalWhere = null, $additionalParams = array(), $orderBy=null) {
		$sql = "select distinct $idField from " . $fromClause;
		list($whereClause,$params) = ADD_Query_Util::generateIdListInSql($idField, $idList, $additionalWhere, $additionalParams);
		$sql.=$whereClause;
		if($orderBy) $sql.=" order by $orderBy";
		
		$edb = ADD::db('encounter');
		$rowIds = $this->fetchColumn($sql, $params,0);
		return is_array($rowIds) ? $rowIds : array($rowIds);
	}
	
	static function getListFromFields($fields,$prepend=null) {
		if($fields && $prepend) {
			array_unshift($fields,$prepend);
		}
		if(!$fields) $fields = array("*");
		$fieldList = implode(",",$fields);
		return $fieldList;
	}
	function fetchEncounterData($fields=null,$encForm, ADD_Query_ResultItem $resultItem) {
		
		$encounterIds = $resultItem->getEncounterIds($encForm['type']);
		//ADD::log()->info("fetchEncounterData Ids:".json_encode($encounterIds));
		list($whereClause,$params) = ADD_Query_Util::generateIdListInSql("e.id", $encounterIds, "e.encounter_form_id=? and e.patient_id=?",array($encForm['id'],$resultItem->getPatientId()));
		
		$fieldList = self::getListFromFields($fields,"e.id");
		
		$sql = "select $fieldList from encounter e left join encounter_form ef on e.encounter_form_id = ef.id "
		. $whereClause;
		//ADD::log()->info("fetchEncounterData SQL:$sql");
		return $this->fetchAll($sql,$params);
	}
	
	function fetchObservationData($fields=null,$encForm, ADD_Query_ResultItem $resultItem,$filterExists=false) {
		$idList = array();	
		$idFields = array("o.encounter_id");
		$idList["o.encounter_id"]=$resultItem->getEncounterIds($encForm['type']);
		
		if($fields) {
			$idFields[]="of.name";
			$idList["of.name"]=$fields;
		}
		list($whereClause,$params) = ADD_Query_Util::generateIdListInSql($idFields,$idList,"e.patient_id=? and of.encounter_form_id=? and of.is_deleted!=1",array($resultItem->getPatientId(),$encForm['id']));

		//$data = array();
		//$whereClause = " where e.patient_id=? and of.encounter_form_id=? and of.is_deleted!=1";
		//$params = array($resultItem->getPatientId(),$encForm['id']);
		$sql = "select o.encounter_id, o.value, o.is_serialized, of.name from observation o "
			. "left join observation_field of on o.observation_field_id = of.id "
			. "left join encounter e on o.encounter_id = e.id "
			. $whereClause;
		return $this->fetchAll($sql,$params);
	}
	
	function fetchPatientData($fields,  ADD_Query_ResultItem $resultItem) {
		$fieldList = self::getListFromFields($fields);
		$patient = $this->fetchFirst("select $fieldList from patient p left join address a on a.id = p.address_id where p.id=?",$resultItem->getPatientId());			
		
		$convertFields = array();
		if(in_array("date_of_death",$fields)) $convertFields[]="date_of_death";
		if(in_array("date_of_birth",$fields)) $convertFields[]="date_of_birth";
		
		if(count($convertFields)>0) {
			ADD_Util::convertDateFromDb($patient, $convertFields);
		}
		return $patient;
	}
}