<?php
use ADD\Pathology\Data\RecordStatuses;

class ADD_Data_PathologyService {
	private $_helper;
	private $_new = false;
	private $log;

	function __construct(ADD_PathologyFactory_Base $recordFactory, \ClinicLE\System\Event\EventDispatcher $events) {
		$this->_recordFactory = $recordFactory;
		$this->log = CLE::log($this);
		$this->events = $events;
	}
	function getFactory() {
		return $this->_recordFactory;
	}
	function doesIdenticalRecordExist($test) {
		$db = ADD::db();
		$encForm = $this->_recordFactory->getEncForm();
		$count = $db->fetchSingleValue("select count(*) from encounter e left join patient p on e.patient_id = p.id where encounter_form_id='".$encForm['id']."' and data_hash=? and p.identifier=?",array($test->getDataAsHash(),$test->getPatientIdent()));
		if($count==1) return true;
		if($count>1) $this->log->warn("Multiple ($count) records match this hash ".$test->getDataAsHash());
		return false;
	}
	
	function populateIdIfTestExists($record) {
		$uniqueObsField = $this->_recordFactory->getUniqueObsField();
		$uniqueId = $record->getUniqueId();
		$matchingTests = ADD::db('encounter')->fetchAll("select encounter_id from observation where observation_field_id=".$uniqueObsField['id']." and value=?",array($uniqueId));
		
		$matchingCount = count($matchingTests);
		if($matchingCount==0) return false;
		
		if(count($matchingCount)>1) {
			$this->log->warn("MULTIPLE (".$matchingCount.") patient tests with ID '{$uniqueId}' found");
			$this->_deleteDuplicateRecords($matchingTests,$uniqueId);
			return false;
		}
		
		
		$record->setRecordId($matchingTests[0]['encounter_id']);
		return true;		
	}
	
	function finalisePatientRecords() {
	}
	
	function persistRecord(ADD_Pathology_Record_Base $record) {
		$em = ADD::sys()->getEncounterManager();
		
		$this->populateIdIfTestExists($record);
	
		if($record->getRecordId()) {
			$er = $em->recordById($record->getRecordId());
			$er->retrieve();
			
			ADD::log($this)->info("Update changed record ".$record->getUniqueId()." (Patient ID '".$record->getPatientIdent()."'");
		}
		else {
			try {
				$parentRecord = $this->retrieveTestParent($record->getPatientIdent());
			}
			catch(ADD_PatientExistsException $ex) {
				$this->log->error("Cannot update record ".$record->getUniqueId()." (Patient ID '"
					.$record->getPatientIdent()."' not in DB)");
				return;
			}
			catch(ADD_NullValueProvidedException $ex) {
				$this->log->error("Cannot update record ".$record->getUniqueId().". (Patient ID is null)");
				return;
			}
			$er = $em->newChild($parentRecord,$this->_recordFactory->getEncType());
		}
		$obsValues = $record->getDataAsObsValues();
		
		$er->setObsValues($obsValues);
		$er->store();
		
	}
	
	function retrieveTestParent($patientIdent) {
		list($path,$test) =  $this->retrieveTestParentByPatientIdent($patientIdent);
		$this->_path = $path;
		return $test;
	}
	function retrieveTestParentByPatientIdent($patientIdent) {
		if(!$patientIdent) throw new ADD_NullValueProvidedException("No patient identifier provided");
		$patient = ADD::db('patient')->getPatientWithIdentifier($patientIdent);
		if(!$patient) throw new ADD_PatientExistsException("Patient with ident '$patientIdent' was not found");
		
		$em = ADD::sys()->getEncounterManager();
		
		$path = $em->domain("path",$patient['id']);
		if(!$path->exists()) $path->store();
		
		$tests = $em->sectionByPatientAndType($patient['id'],"path-tests");
		if(!$tests->exists()) $tests->store();
		return array($path,$tests);
	}
	function _deleteDuplicateRecords($matchingTests,$uniqueId) {
		ADD::log($this)->warn("Removed duplicate tests with ID '{$uniqueId}'");
			
		foreach($matchingTests as $matchingTest) {
			$er = ADD::sys()->getEncounterManager()->recordById($matchingTest['id']);
			$er->retrieve();
			$er->delete();
		}
	}
	function auditPathImport($patientId,$nochange,$created,$updated,$total) {
		ADD::db('audit')->createAudit(array(
			"record_type"=>lcase($this->getFactory()->getTestType()),
			"record_id"=>$patientId,
			"action_info"=>ADD_Util::serialize(array("nochange"=>$nochange,"created"=>$created,"updated"=>$updated,"total"=>$total)),
			"action"=>"import"
		));
	}
	
	function retrievePatientStatus($patientId) {
		$em = ADD::sys()->getEncounterManager();
		
		list($path,$test) = $this->retrieveTestParentByPatientIdent($patientId);
		$testSummary = $em->child($test,$this->getFactory()->getSummaryType());
		$testSummary->retrieve();
		return $testSummary;
	}
	private function getTestSummaryRecord($test) {
		$em = ADD::sys()->getEncounterManager();
		try {
			return $em->child($test, $this->getFactory()->getSummaryType());
		}
		catch(\ClinicLE\Data\Exception\MultipleRecordsException $ex) {
			$testSummaries = $em->children($test, $this->getFactory()->getSummaryType());
			$testSummary = array_shift($testSummaries);
			$this->log->warn("Deleting superfluous test summmary records (" . count($testSummaries) . ")");
			foreach ($testSummaries as $removeSummary) {
				$removeSummary->delete();
			}
			return $testSummary;
		}
	}
	function updatePatientStatus($patientIdents, $status, $message = "") {
		ADD::db()->beginTxn();

		if(!is_array($patientIdents)) {
			$patientIdents = array($patientIdents);
		}

        $events = $this->events;

        $this->log->info("Update patient status in " . count($patientIdents) . " with changed data");

        foreach($patientIdents as $patientIdent) {
			$now = ADD_Field_Util::now();
			$event = new \ADD\Pathology\Event\MapPatientEvent($patientIdent);
			$events->dispatch(\ADD\Pathology\Event\PathologyEvents::MAP_PATIENT,$event);
            if($event->isPropagationStopped()) {
                continue;
            }
			list($path,$test) = $this->retrieveTestParentByPatientIdent($event->getMappedPatientIdent());
			$testSummary = $this->getTestSummaryRecord($test);

			$testSummary->setObsValue("transfer_status",$status);
			$testSummary->setObsValue("status_timestamp",$now);
			$testSummary->setObsValue("status_message",$message);
			
			if($status == RecordStatuses::COMPLETE) {
				$testSummary->setObsValue("complete_timestamp",$now);
			}
			$testSummary->store();
		}
		ADD::db()->commitTxn();
	}
}
?>
