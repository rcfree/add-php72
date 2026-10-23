<?php
namespace ADD\Pathology\Command;
use ADD\Pathology\Event\PathologyEvents;
use ADD\Pathology\Event\PatientPopulateEvent;
use CLE;
use ADD\Pathology\Data\RecordStatuses;
use ClinicLE\Common\Util;
use DateTime;
use ClinicLE\System\Service\CoreServices;

class PathDedupCommand extends \ClinicLE\Command\AbstractCommand  {
	private $_patientFile;
	private $dataSource;

	function getOptsList() {
		return "n";
	}

	function getDescription() {
		return "De-duplicate pathology records";
	}
	
	function runCommand(\ClinicLE\Common\Config\AbstractConfig $options) {
		$log = CLE::log($this);
		
		$log->running();

		$this->_deleteDuplicateRecords("path-tests-haem", $options->has("n"));
        $this->_deleteDuplicateRecords("path-tests-micro", $options->has("n"));


        $log->complete();
	}

	function _deleteDuplicateRecords($type, $dryrun) {
        $log = CLE::log($this);

        $conn = CLE::svc(CoreServices::MAIN_CONNECTION);

        $dupPatients = $conn->fetchAll("select e.patient_id, p.identifier from encounter e left join encounter_form ef on ef.id = e.encounter_form_id left join patient p on p.id = e.patient_id".
            " where e.is_deleted=0 and type='$type' group by e.patient_id having count(e.id)>1", array());

        if ($dryrun) {
        	$log->info("Dryrun deduplication");

        }
        $log->info("** De-duplicate ".count($dupPatients)." '$type' records");
        $edb = CLE::db("encounter");

        foreach($dupPatients as $dupPatient) {
            $dupRows = $conn->fetchAll("select e.id from encounter e left join encounter_form ef on e.encounter_form_id = ef.id where e.patient_id=? and ef.type='$type' order by e.date_created",array($dupPatient['patient_id']));
            array_shift($dupRows);

            if (!$dryrun) {
                $edb->deleteRelatedEncounters($dupRows);
            }
            $log->debug("Delete patient '" . $dupPatient['identifier'] ."' (".count($dupRows)." encs)");

        }
	}

	function runAtEnd($exception=null) {

	}

}

?>
