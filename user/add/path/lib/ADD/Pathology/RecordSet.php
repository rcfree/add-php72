<?php

class ADD_Pathology_RecordSet {
	private $_records=array();
	private $_maxSize;
	private $_pathologyService;
	public $created = array();
	public $updated = array();

	function __construct(ADD_Data_PathologyService $pathologyService, $maxSize=1000) {
		$this->_maxSize=$maxSize;
		$this->_pathologyService = $pathologyService;
	}
	
	function addRecord(ADD_Pathology_Record_Base $record=null) {
		$this->_records[$record->getUniqueId()]=$record;
	}
	function getRecords() {
		return $this->_records;
	}
	function getRecord($data) {
		return ADD_Util::val($data['UNIQUE_TEST_ID'],$this->_records);
	}
	function getMaxSize() {
		return $this->_maxSize;
	}
	function isSetFull() {
		if (count($this->_records)>=$this->_maxSize) return true;
		return false;
	}
	function clearRecords() {
		$this->_records=null;
	}
	
	public function persistRecords() {
		$records = $this->getRecords();
		$log = ADD::log($this);
		$identical=0;
		$created=0;
		$updated=0;
		
		if(!$records) return;
		foreach($records as $id => $record) {
			if($this->_pathologyService->doesIdenticalRecordExist($record)) {
				$identical++;	
				continue;
			}
			$this->_pathologyService->persistRecord($record);
			if($record->getRecordId()) {
				$this->updated[] = $record;
			}
			else {
				$this->created[] = $record;
			}
		}
		$log->info("Persisted ".$this->countRecords()." records - created: " . count($created). ", updated:".count($updated) . ", identical:".$identical);
		$this->clearRecords();
	}	
	public function countRecords() {
		return count($this->_records);
	}
	public function getPersistedPatientIds() {
	    $patientIds = array();
	    foreach($this->created as $created_item) {
	        $patientIds[$created_item->getPatientIdent()] = true;
        }
        foreach($this->updated as $updated_item) {
            $patientIds[$updated_item->getPatientIdent()] = true;
        }
        return array_keys($patientIds);
    }
}