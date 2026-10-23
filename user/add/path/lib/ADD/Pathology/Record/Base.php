<?php



abstract class ADD_Pathology_Record_Base {
	protected $_rowData=array();
	protected $_recordId;
	protected $_changed=false;
	protected $_patientIdent;
	protected $_encType;
	protected $_mapping;
	protected $_uniqueId;
	
	function hasData() {
		if(!$this->_rowData) return false;
		if(ADD_Util::isEmpty($this->_rowData)) return false;
		return true;
	}
	
	function setPatientIdent($patientIdent) {
		$this->_patientIdent = $patientIdent;
	}
	function getPatientIdent() {
		return $this->_patientIdent;
	}
	function setDataFromRow($rowData) {
		$ident = ADD_Util::val('SYSTEM_NUMBER',$rowData);
		if(!$ident) throw new ADD_NullValueProvidedException("'SYSTEM_NUMBER' is null in the source data");
		$this->setPatientIdent($ident);
		$this->_updateRowData($rowData);
		$this->_uniqueId = $this->_rowData['unique_test_id'];
	}
	function _updateRowData($rowData) {
		$log = ADD::log($this);
			
		$rowData = ADD_Pathology_Util::remapArrays($this->_mapping,$rowData);
		
		$collDateTime = ADD_Util::val('coll_date_time',$rowData);
		if($collDateTime) {
			$dt = new DateTime($collDateTime);
			$rowData['coll_date_time']=$dt->format("d/m/Y H:i");
		}
		$this->getDataAsHash();
		if($this->_rowData && $this->_rowData==$rowData) {
			return;
		}
		$this->_rowData = $rowData;
		$this->_changed=true;
	}
	
	function setDataFromResultItem(ADD_Query_ResultItem $resultItem) {
		$ident = $resultItem->getPatientData('identifier');
		if(!$ident) throw new ADD_NullValueProvidedException("'identifier' is null in the source data");
		$this->setPatientIdent($ident);
		
		$encounterData = $resultItem->getEncounterData();
		$encTypes = array_keys($encounterData);
		$encType = $encTypes[0];
		
		$encounters = $encounterData[$encType];
		$encIds = array_keys($encounters);
		$obsValues = $encounters[$encIds[0]];
		$this->_recordId = $encIds[0];
		$this->_updateOtherDataFromValues($obsValues);
		
		$rowData = ADD_Pathology_Util::limitArrays(array_values($this->_mapping),$obsValues);
		
		$this->_recordId = $encIds[0];
		$this->_uniqueId = $rowData['unique_test_id'];
		$this->_rowData=$rowData;
	}
	function getUniqueId() {
		return $this->_uniqueId;
	}
	
	function hasDataChanged($rowData) {
	}
	/*
	function hasChanged() {
		return $this->_changed;
	}
	 * 
	 */
	function setChanged($changed) {
		$this->_changed=$changed;
	}
	function getRecordId() {
		return $this->_recordId;
	}
	function setRecordId($recordId) {
		$this->_recordId = $recordId;
	}
	
	function getDataAsObsValues() {
		$obsValues = $this->_rowData;;
		$this->_populateValuesWithOtherData($obsValues);
		
		return $obsValues;
	}
	function getDataAsHash() {
		return ADD_Util::hashData($this->getDataAsObsValues());
	}
	function _populateValuesWithOtherData(&$obsValues) {
	}
	function _updateOtherDataFromValues($obsValues) {
	}
}