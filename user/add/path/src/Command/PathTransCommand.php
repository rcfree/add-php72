<?php
namespace ADD\Pathology\Command;
use ADD\Pathology\Event\PathologyEvents;
use ADD\Pathology\Event\PatientPopulateEvent;
use CLE;
use ADD\Pathology\Data\RecordStatuses;
use ClinicLE\Common\Util;
use DateTime;
class PathTransCommand extends \ClinicLE\Command\AbstractCommand  {
	private $_patientFile;
	private $dataSource;
	
	function getOptsList() {
		return "pulid";
	}

	function getDescription() {
		return "Transfer pathology data";
	}
	
	function runCommand(\ClinicLE\Common\Config\AbstractConfig $options) {
		$add = CLE::sys();
		$log = CLE::log($this);
		
		$log->running();
        
		try {

			$mod = CLE::sys()->getModule("path");
			

			$config = $add->getConfig()->getSection("Pathology");
			if(!$config) throw new \ADD_SettingNotDefinedException("No 'Pathology' section defined in ADD config");
			$log->info("Populate patient IDs");
			$this->populatePatientIds($options,$config);
			if ($options['d']) {
				$dt = new DateTime($options['d']);
				$config['date_since'] = $dt->format("Y-m-d");
			}
			$log->info("Updating haem tests");
			$this->_useFactoryToUpdateTests(new \ADD_PathologyFactory_Haem($config, $add->getEvents()));
			$log->info("Updating micro tests");
			$this->_useFactoryToUpdateTests(new \ADD_PathologyFactory_Micro($config, $add->getEvents()));
			CLE::db('encounter')->commitTxn();
			if($this->dataSource && $this->dataSource->wasLocked()) {
				$this->dataSource->setAvailable();
			}
		}
		catch(\Exception $ex) {
			$log->fatal("Unexpected error:".$ex->getMessage().$ex->getTraceAsString());
		}
	   $log->complete();
	}
	
	function runAtEnd($exception=null) {
		if($this->dataSource && $this->dataSource->wasLocked()) {
			$this->dataSource->setAvailable();
		}

		if($this->_patientFile && file_exists($this->_patientFile)) {
			unlink($this->_patientFile);
		}
		
	}
	
	function _useFactoryToUpdateTests(\ADD_PathologyFactory_Base $factory) {
		CLE::db()->beginTxn();
		
		$ptm = new \ADD_Pathology_TestManager($factory,5000,CLE::sys()->getEvents());

		try {
			$ptm->init();
			$this->dataSource = $ptm->getDataSource();
		
		
			$ptm->preventConcurrentRuns(true);
			$ptm->removeDuplicateDomains();
			$ptm->updateTests();
		}

		catch(\Exception $ex) {
			CLE::db()->rollbackTxn();
			$status = is_a($ex,"ADD_Pathology_Exception_SourceTimeoutException") ? RecordStatuses::ABORTED : RecordStatuses::ERROR;
			$ptm->getPathologyService()->updatePatientStatus($factory->getConfig()->assertSetting("patients"),$status,$ex->getMessage());
			CLE::db()->commitTxn();
			throw $ex;
		}
			
		CLE::db()->commitTxn();
	}
	
	function populatePatientIds($options,$config) {
		$event = new PatientPopulateEvent();
		$events = CLE::sys()->getEvents();

		if($options->has('p')) {
			$patient = CLE::db('patient')->getPatient($options->get('p'));
			if(!$patient) throw new \Exception("Patient with ID ".$options->get('p')." does not exist");
			$event->setPatients(array($patient));

		}
		elseif($options->has('i')) {
			$patient = CLE::db('patient')->getPatientWithIdentifier($options->get('i'));
			if(!$patient) throw new \Exception("Patient with identifier ".$options->get('i')." does not exist");
			$event->setPatients(array($patient));
		}
		elseif($options->has('u')) {
			$patients = CLE::db('patient')->fetchAll("select p.id,identifier from patient p left join domain d on d.patient_id = p.id left join domain_type dt on d
.domain_type_id = dt.id where dt.name=?",array("path"));
			$event->setPatients($patients);
		}
		else {
			throw new \InvalidArgumentException("No patient selection/populate options were selected");
		}
		$events->dispatch(PathologyEvents::POPULATE_PATIENTS,$event);
        $patients = $event->getPatients();

        if(count($patients==1)) {
			$this->_patientFile=CLE::sys()->getPath()."/tmp/.path-patient-".$patients['0']['id'];
        }
        else {
			Util::serialize($patients);
            $this->_patientFile=CLE::sys()->getPath()."/tmp/.path-patient-".sha1($patients);
        }
        $config->set('patients',array_map(function($patient) {
            return $patient['identifier'];
        },$patients));
	}
}

?>
