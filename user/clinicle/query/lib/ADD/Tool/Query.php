<?php

class ADD_Tool_Query {
	private $_records = array();
	
	public function __construct($records) {
		$this->_records=$records;
	}
	static function childRecordsWithType($parent,$encType) {
		$edb = ADD::db('encounter');
		$sql = "select e.* from encounter e 
				left join encounter_form ef on e.encounter_form_id=ef.id
				left join observation o on o.encounter_id = e.id
				left join observation_field of on o.observation_field_id = of.id
				where e.parent_encounter_id=? and ef.type=?";
		$encs = $edb->fetchAll($sql,array($parent->getId(),$encType));
		$records=array();
		$em = ADD::sys()->getEncounterManager();
		foreach($encs as $enc) {
			$record = $em->recordById($enc['id']);
			$record->setParent($parent);
			$record->setType($encType);
			$records[$enc['id']]=$record;
		}
		$obj = new ADD_Tool_Query($records);
		return $obj;
	}
	
	static function recordsWithType($encType) {
		$edb = ADD::db('encounter');
		$sql = "select e.* from encounter e 
				left join encounter_form ef on e.encounter_form_id=ef.id
				where ef.type=? and e.patient_id!=7583";
		$encs = $edb->fetchAll($sql,array($encType));
		$records=array();
		$em = ADD::sys()->getEncounterManager();
		foreach($encs as $enc) {
			$record = $em->recordById($enc['id']);
			$record->setParent(null);
			$record->setType($encType);
			$records[$enc['id']]=$record;
		}
		$obj = new ADD_Tool_Query($records);
		return $obj;
	}
	
	static function recordsWithPatientAndType($patientId, $encType) {
		$edb = ADD::db('encounter');
		$sql = "select e.* from encounter e 
				left join encounter_form ef on e.encounter_form_id=ef.id
				where ef.type=? and e.patient_id=$patientId";
		$encs = $edb->fetchAll($sql,array($encType));
		$records=array();
		$em = ADD::sys()->getEncounterManager();
		foreach($encs as $enc) {
			$record = $em->recordById($enc['id']);
			$record->setParent(null);
			$record->setType($encType);
			$records[$enc['id']]=$record;
		}
		$obj = new ADD_Tool_Query($records);
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
	
	public function records() {
		return $this->_records;
	}
	public function patients() {
		$patientIds = array();
		foreach($this->_records as $record) {
			$patientIds[$record->getPatient()]=true;
		}
		$pdb = ADD::db('patient');
		foreach($patientIds as $patientId => $flag) {
			$patients[$patientId]=$pdb->getPatient($patientId);
		}
		return $patients;
	}
	public function countPatients() {
		return count($this->patients());
	}
	public function singleRecord() {
		return ADD_Util::val(0,$this->_records);
	}
	public function countRecords() {
		return count(array_keys($this->_records));
	}
	public function intersect($encounterQuery) {
		$intersect = array();
		foreach($encounterQuery->results() as $record) {
			if(isset($this->_records[$record->getId()])) {
				$intersect[$record->getId()]=$record;
			}
		}
		return new ADD_Tool_Query($intersect);
	}
}