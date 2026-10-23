<?php
use ADD\Pathology\Data\RecordStatuses;

class ADD_Pathology_TestManager {
	private $_dataSource;
	private $_pathologyService;
	private $_recordSet;
	
	public function __construct(ADD_PathologyFactory_Base $factory, $maxSize=2000, \ClinicLE\System\Event\EventDispatcher $events) {
		$this->_factory = $factory;
		$this->_maxSize = $maxSize;
        $this->events = $events;
	}
	
	public function init() {
		$this->_pathologyService = $this->_factory->newPathologyService();
		$this->_dataSource = $this->_factory->newDataSource("Warehouse");

		$this->_recordSet = $this->_factory->newRecordSet($this->_pathologyService,$this->_maxSize);
	}
	
	public function preventConcurrentRuns($waitForUnlock=false,$waitCount=0) {
		$config = $this->_factory->getConfig();
        $log = ADD::log($this);
        
		if($this->_dataSource->isAvailable()) {
			$log->running();
		
			//$this->_pathologyService->updatePatientStatus($config->assertSetting("patients"),RecordStatuses::IN_PROGRESS);
			$this->_dataSource->initDataSource();
			return;
		}
		
		if(!$waitForUnlock) {
			throw new Exception("It seems there is a current DB read. You may need to clear the LRBRU_PATHOLOGY_LOCK table");
		}
		$pollTime = $config->getSetting('poll_time',10);
		$maxWait = $config->getSetting('max_wait',300);
		if($waitCount>=($maxWait/$pollTime)) {
			$msg = "The other DB read has taken >".$maxWait." seconds to complete.";
			throw new ADD_Pathology_Exception_SourceTimeoutException($msg.". Aborting now.");
		}
		$waitCount++;
		
		$retryMsg = "Retry in $pollTime s (".$waitCount." ".ADD_Util::pluralize($waitCount,"attempt")."/".floor($maxWait/$pollTime)." allowed)";
		$log->warn($retryMsg);
		$log->pending();
		
		sleep($pollTime);
		$this->preventConcurrentRuns(true,$waitCount);
	}

	public function updateTests()
    {
        $log = ADD::log();
        $second = false;
        $this->_batchNo = 1;
        $log = ADD::log($this);
        $itemTotal = $this->_dataSource->getTestItemTotal();
        $log->info("Batch size " . $this->_recordSet->getMaxSize());
        $log->info("Total records in DataSource:" . $itemTotal);
        $log->info("Maximum number of batches:" . ceil($itemTotal / $this->_recordSet->getMaxSize()));
        $rowCount = 0;
        $identical = 0;
        $seenPreviously = array();
        $prevUniqueId = null;
        $factory = $this->_factory;

        $persistedPatientIds = array();

        while ($testItem = $this->_dataSource->nextTestItem()) {
            $event = new \ADD\Pathology\Event\TestItemEvent($testItem);
            $this->events->dispatch(\ADD\Pathology\Event\PathologyEvents::RETRIEVE_TEST_ITEM, $event);
            if ($event->isPropagationStopped()) {
                continue;
            }
            $rowCount++;
            $record = $this->_recordSet->getRecord($event->getTestItem());
            if (!$record) {
                $record = $factory->newRecord($event->getTestItem());
                $this->_recordSet->addRecord($record);
            }
            $record->updateFromRowData($event->getTestItem());

//            if ($rowCount % 100 == 0) {
//                $log->info("Current row:" . $rowCount . "/" . $itemTotal);
//            }

            if ($prevUniqueId != $record->getUniqueId()) {
                $prevUniqueId = $record->getUniqueId();
                if ($this->_recordSet->isSetFull()) {
                    $this->_persistBatch();
                    $persistedPatientIds = array_merge($persistedPatientIds, $this->_recordSet->getPersistedPatientIds());
                }
            }
        }

        $this->_persistBatch();
        $persistedPatientIds = array_unique(array_merge($persistedPatientIds, $this->_recordSet->getPersistedPatientIds()));

        $this->_finalisePatientTests();
        $this->getPathologyService()->updatePatientStatus($persistedPatientIds, RecordStatuses::COMPLETE);
    }
	function removeDuplicateDomains() {
		$patientIdents = $this->_dataSource->getPatients();
		$edb = ADD::db('encounter');
		$log = ADD::log($this);
		$events = $this->events;
		foreach($patientIdents as $patientIdent) {
            $event = new \ADD\Pathology\Event\MapPatientEvent($patientIdent);
            $events->dispatch(\ADD\Pathology\Event\PathologyEvents::MAP_PATIENT,$event);
            if($event->isPropagationStopped()) {
                continue;
            }
            $paths = $edb->fetchAll("select d.* from domain d left join patient p on d.patient_id = p.id left join domain_type dt on d.domain_type_id = d.id where d.is_deleted!=1 and p.identifier=? and dt.name=?",array($event->getMappedPatientIdent(),"path"));
			if(count($paths)<2) continue;
			array_shift($paths);
			$edb->purgeRelatedEncounters($paths);
			$log->warn("Duplicate existing 'path' records found. They have been purged");
		}
	}
	function _persistBatch() {
		$log = ADD::log($this);	
		$recCount = $this->_recordSet->countRecords();
		if($recCount==0) {
			return;
		}
		$log->info("Persist batch ".$this->_batchNo."($recCount records)");
		$this->_recordSet->persistRecords();
		$this->_batchNo++;
	}
	
	function _finalisePatientTests() {
		$patientIdents = $this->_dataSource->getPatients();
		$encForm = $this->_factory->getEncForm();
		$edb = ADD::db('encounter');
		ADD::log()->info("Finalising patient tests");
		$noTestCount=0;
        $events = $this->events;
		foreach($patientIdents as $patientIdent) {
            $event = new \ADD\Pathology\Event\MapPatientEvent($patientIdent);
            $events->dispatch(\ADD\Pathology\Event\PathologyEvents::MAP_PATIENT,$event);
            if($event->isPropagationStopped()) {
                continue;
            }
            $mappedIdent = $event->getMappedPatientIdent();
			$noTests = $edb->fetchSingleValue("select count(*) from encounter e left join patient p on e.patient_id = p.id where p.identifier=? and e.encounter_form_id=?",array($mappedIdent,$encForm['id']));
			if($noTests!=0) continue;
			$noTestCount++;
			$this->_pathologyService->retrieveTestParent($mappedIdent);
		}
		ADD::log()->info("$noTestCount patient(s) had no ".$this->_factory->getTestType()." tests");
	}
	
	function getDataSource() {
		return $this->_dataSource;
	}
	
	function getPathologyService() {
		return $this->_pathologyService;
	}
	
}
	