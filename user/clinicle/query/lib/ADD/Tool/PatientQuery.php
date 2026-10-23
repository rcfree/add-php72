<?php

class ADD_Tool_PatientQuery {
	private $_patients = array();
	
	public function __construct($records) {
		$this->_patient=$patients;
	}
	static function patientsWithChildRecords($parent,$encType) {
		$edb = ADD::db('encounter');
		$sql = "select distinct e.patient_id from encounter e 
				left join encounter_form ef on e.encounter_form_id=ef.id
				left join observation o on o.encounter_id = e.id
				left join observation_field of on o.observation_field_id = of.id
				where e.parent_encounter_id=? and ef.type=?";
		$encs = $edb->fetchAll($sql,array($parent->getId(),$encType));
		
		$patients=array();
		
		foreach($encs as $enc) {
			$records[$enc['patient_id']]=$record;
		}
		$obj = new ADD_Tool_PatientQuery($records);
		return $obj;
	}
	
	static function patientsWithRecords($encType) {
		$edb = ADD::db('encounter');
		$sql = "select distinct e.patient_id from encounter e 
				left join encounter_form ef on e.encounter_form_id=ef.id
				where ef.type=? and e.patient_id!=7583";
		$encs = $edb->fetchAll($sql,array($encType));
		$patients=array();
		foreach($encs as $enc) {
			$record = $em->recordById($enc['id']);
			$record->setParent(null);
			$record->setType($encType);
			$records[$enc['id']]=$record;
		}
		$obj = new ADD_Tool_PatientQuery($records);
		return $obj;
	}
	
	public function filterByValue($obsField,$obsValue) {
		$filteredRecords = array();
		foreach($this->_records as $id => $record) {
			$record->retrieve();
			$recObsValue = $record->getObsValue($obsField);
			if(is_array($recObsValue)) {
				if(!in_array($obsValue,array_keys($recObsValue))) continue;
			}
			else {
				if($obsValue!=$recObsValue) {
					continue;
				}
			}
			$filteredRecords[$id]=$record;
		}
		$this->_records = $filteredRecords;
		return $this;
	}	
	
	public function filterByNotNull($obsField) {
		$filteredRecords = array();
		foreach($this->_records as $id => $record) {
			$record->retrieve();
			if(ADD_Util::isEmpty($record->getObsValue($obsField))) {
				continue;
			}
			$filteredRecords[$id]=$record;
		}
		$this->_records = $filteredRecords;
		return $this;
	}
	
	public function results() {
		return $this->_records;
	}
	public function singleResult() {
		return ADD_Util::val(0,$this->_records);
	}
	public function count() {
		return count($this->_records);
	}
	public function intersect($encounterQuery) {
		$intersect = array();
		foreach($encounterQuery->results() as $record) {
			if(isset($this->_records[$record->getId()])) {
				$intersect[$record->getId()]=$record;
			}
		}
		return new ADD_Tool_EncounterQuery($intersect);
	}
}