<?php
class ADD_Import_EncounterRow {
	private $_patient = null;
	private $_page = null;
	private $_row = array();
	private $_obs = array();
	private $_identifier = null;
	private $_pageList = null;
	private $_patIdent = null;
	private $_obsHeadings = array();
	
	function __construct($obsHeadings,$pageList) {
		
		$this->_obsHeadings = $obsHeadings;
		$this->_pageList = $pageList;
	}
	function populate($row) {
		$add = ADD::sys();
		
		//get key info from line
		list($patIdent,$parent,$action,$identifier)=ADD_Util::getArrayValue(array("patient_id","parent","action","id"),$row);
		if (strlen($parent)==0) $parent = null;
		
		//get patient matching ID
		if ($this->_patIdent != $patIdent) {
			$db = $add->db('patient');
		
			$patient = 	$db->getPatientWithIdentifier($patIdent);
			if (!$patient) {
				global $log;
				$log->error("Patient with ident '$patIdent' was not found");
				return false;
			}
			$pid = $patient->getId();
			$add->setPatientId($pid);
			$this->_patient = $patient;
			$this->_patIdent = $patIdent;
		}

		$this->_page = $this->_pageList[$row['page']];
		
		$this->_row = $row;
		$obs = $this->determineObsValues();
		$this->_obs = $obs;
		$this->_parent=$parent;
		$this->_action=$action;
		$this->_identifier=$identifier;
		return true;
		
	}
	function getPatient() {
		return $this->_patient;
	}
	function getParent() {
		return $this->_parent;
	}
	function getObs() {
		return $this->_obs;
	}
	function getPage() {
		return $this->_page;
	}
	function correctHash() {
		$patHash=ADD_Util::getArrayValue("patient_hash",$this->getRow());
		
		$pat = $this->_patient;
		$dbMd5 = md5($pat->getIdentifier().$pat->getFirstName().$pat->getLastName().$pat->getDateOfBirth());
		if ($dbMd5!=$patHash) {
			global $log;
			$log->error("ERROR - patient hashes (db:$dbMd5 != file:$patHash) do not match");
			return false;
		}

		return true;
	}
	
	function determineObsValues() {
		$obsHeadings = $this->_obsHeadings;
		$page = $this->getPage();
		if (!$page->hasComponent('Form')) return array();
	  	$form = $page->getComponent('Form');
				
		$obs = array();
		$row = $this->getRow();
		global $log;
		foreach($form->getFieldsByName() as $name => $field) {
			if (!$field->isStored() || $field->getSetting('no_store')) continue;
			$propVal = ADD_Util::getArrayValue($name,$row);
			
			if (!isset($obsHeadings[$name])) continue;
			if ($obsHeadings[$name]["type"]=="lookup") {
				$field->setLabel($propVal);
			}
			else {
				$field->setValue($propVal);
			}

			$obs[$name]=$field->getValue();
		}
		return $obs;		
	}
	function getAction() {
		return $this->_action;
	}
	function getRow() {
		return $this->_row;
	}
	function getIdentifier() {
		return $this->_identifier;
	}
	function extractIdForParent($idList) {
			
		$parent = $this->getParent();
		if (is_numeric($parent)) return $parent;
		if ($parent && !isset($idList[$parent])) {
			global $log;
			$log->error("ERROR - parent placeholder '$parent' not previously defined in file");
			return null;
		}
		$id = ADD_Util::getArrayValue($parent,$idList);
		return $id;
	}
	function findMatchingEncounter() {
		$page = $this->getPage();
		
		if (is_numeric($this->getIdentifier())) {
			$add = & ADD::sys();
			return $add->db('encounter')->getEncounter($this->getIdentifier());
		}
		
		$ec = $page->hasComponent('Encounter') ? $page->getComponent('Encounter') : $page->getComponent('Content');
		return $ec->findMatchingEncounter($this);
	}
	function updateEncounter($encList) {
		$add = ADD::sys();
		$add->setState('new',false);
		global $log;
		$pid = $add->getPatientId();
		gc_enable();
		$eid = $this->extractIdForParent($encList);
					
		$add->setParam('eid',$eid);
		
		$enc = $this->findMatchingEncounter();
		$page = $this->getPage();
		$obs = $this->getObs();
		
		$created=false;
		if (!$enc) {
			$add->setState('new',true);
			$ec = $page->hasComponent('Encounter') ? $page->getComponent('Encounter') : $page->getComponent('Content');
			list($created,$enc,$createdObs,$updatedObs) = $ec->storeEncounter($obs,$page);
		}
		
		$log->debug(($created ? "NEW" : "EXISTING")." encounter -> id:{$enc->getId()}, patient:".$this->_patIdent." '{$page->getName()}' ($pid), parent:".$enc->getParentEncounterId()." => obs:".json_encode($obs));

		
		unset($createdObs);
		unset($updatedObs);
		return array($created,$enc);
	}
	
	function __destruct() {
		if ($this->_patient) $this->_patient->clearAllReferences(true);
		unset($this->_patient);
		unset($this->_page);
		unset($this->_row);
		unset($this->_obs);
		gc_collect_cycles();
	}
}
?>
