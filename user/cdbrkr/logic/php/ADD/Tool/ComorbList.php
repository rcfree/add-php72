<?php 
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Data\Record\EncounterRecord;
class ADD_Tool_ComorbList {
	private $_existingConditions=array();
	private $_primaryConditions;
	private $_existingEncType;
	function __construct(ADD_EncounterRecord $parentRecord, $existingEncType="cdbrkr-wspl-comorbitem") {
		$this->_parentRecord = $parentRecord;
		$this->_existingEncType = $existingEncType;
	}
	
	function initExistingConditions() {
		$repo = ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY);
		$query = $repo->createQuery();
		$query->filterByType($this->_existingEncType);
		$query->filterByEncounterField(EncounterRecord::SECTION_FIELD,$this->_parentRecord->getId());
		$query->addEncounterFields(array("id"));
		$query->addObservationFields(array("icd10_code","primary_diagnosis"));
		
		$queryRunner = ADD::sys()->getService(CoreServices::QUERY_RUNNER);
		$data = $queryRunner->getRows($query);
		$comorbs = array();
		$primaryConditions = array();
		foreach($data as $item) {
			$icd10 = $item['icd10_code'];
			$comorbs[$icd10]=$item['id'];
			$primaryConditions[$icd10]=$item['primary_diagnosis'];
		}
		$this->_existingConditions = $comorbs;
		$this->_primaryConditions = $primaryConditions;
	}
	function getExistingConditions() {
		return $this->_existingConditions;
	}
	function getRecordIdForCode($code) {
		return ADD_Util::val($code,$this->_existingConditions);
	}
	
	function isConditionSelected($code) {
		return array_key_exists($code,$this->_existingConditions);
	}
	function isConditionPrimary($code) {
		return ADD_Util::val($code,$this->_primaryConditions);
	}
}
?>