<?php
class ADD_Data_ConsentRecord {
	private $_encounterRecord;
	private $_log="";
	private $_contactID=null;
	private $_status;
	
	function __construct(ADD_EncounterRecord $encounterRecord) {
		$this->_encounterRecord = $encounterRecord;
		$this->_encounterRecord->retrieve(true);
			
        if ($this->_encounterRecord->exists()) {
			$this->refresh();
			return;
		}
		
		$this->_encounterRecord->setObsValue("civi_status","");
		$this->_encounterRecord->setObsValue("civi_log","");
		$this->_encounterRecord->setObsValue("civi_cid","");
        $this->_encounterRecord->store();
	}
	
	function addToLog($item) {
		$this->_log.=date("Y-m-d H:i:s")." $item\n";
	}
	function addLogAndStatus($item=null,$status=null,$cid=null) {
		if ($item) {
			$this->addToLog($item);
		}
		if ($status) {
			$this->_status=$status;
		}
		if($cid) {
			$this->_contactID=$cid;
		}
		$this->store();
	}
	
	function store() {
		ADD::db()->beginTxn();
		$this->_encounterRecord->retrieve(true);
		$this->_encounterRecord->store(
			array(
				"civi_status"=>$this->_status,
				"civi_log"=>$this->_log,
				"civi_cid"=>$this->_contactID
			)
		);
		ADD::db()->commitTxn();
	}
	function refresh() {
		$this->_encounterRecord->retrieve(true);
		$this->_status = $this->_encounterRecord->getObsValue('civi_status');
		$this->_log = $this->_encounterRecord->getObsValue('civi_log');
		$this->_contactID = $this->_encounterRecord->getObsValue('civi_cid');
	}
	function getField($fieldName) {
		return $this->_encounterRecord->getObsValue($fieldName);
	}
	function getStatus() {
		return $this->_status;
	}
	function getContactID() {
		return $this->_contactID;
	}
	function getLog() {
		return $this->_log;
	}
}
