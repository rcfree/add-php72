<?php
use ClinicLE\Common\Config\AbstractConfig;

abstract class ADD_PathologyFactory_Base {
	private $_config;
	private $_encForm;
	private $_uniqueObsField;

	function __construct(AbstractConfig $config, \ClinicLE\System\Event\EventDispatcher $events) {
		$this->_config = $config;
		$this->events = $events;
	}
	function getTestType() { }
	function getEncType() { }
	function newRecord($data) {
		$record = ADD_Util::loadClass("ADD_Pathology_Record_".$this->getTestType());
		$record->setDataFromRow($data);
		return $record;
	}
	
	function newPathologyService() {
		return new ADD_Data_PathologyService($this,$this->events);
	}
	
	function newRecordSet(ADD_Data_PathologyService $pathologyService, $maxSize=5000) {
		return new ADD_Pathology_RecordSet($pathologyService,$maxSize);
	}
	function getEncForm() {
		if(!$this->_encForm) {
			$this->_encForm = ADD::db('encounter')->getEncounterFormWithType($this->getEncType());
		}
		return $this->_encForm;
	}
	function getUniqueObsField() {
		if(!$this->_uniqueObsField) {
			$this->_uniqueObsField = ADD::db('encounter')->getFieldFromEncounterForm($this->getEncType(), "unique_test_id");
		}
		return $this->_uniqueObsField;
	}
	
	function newDataSource($sourceType) {
		return ADD_Util::loadClass("ADD_Pathology_DataSource_".$sourceType."_".$this->getTestType(),$this->_config);
	}
	function getConfig() {
		return $this->_config;
	}
	abstract function getSummaryType();
}
?>