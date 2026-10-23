<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Repository;

use ClinicLE\Data\Record\EncounterRecord;
use CLE;
use ClinicLE\Common\Exception\NullValueException;
use ADD_Util;
use ClinicLE\System\Service\CoreServices;
/**
 * Description of EncounterRepository
 *
 * @author rob
 */
class EavEncounterRepository extends AbstractEncounterRepository {

    function retrieveEncounter($encounter, $force = false) {
        $edb = CLE::db('encounter');
        if ($encounter->isRetrieved() && !$force) {
            return;
        }
        $this->populateEncounter($encounter);
        if (!$encounter->getEncounter()) {
            $encounter->setObsValues(array());
            $encounter->setNew(true);
            return $encounter;
        }
        
        if (!$encounter->getSection() && $encounter->getEncData(EncounterRecord::SECTION_FIELD)) {
            $per = CLE::sys()->getEncounterManager()->sectionById($encounter->getEncData(EncounterRecord::SECTION_FIELD));
            CLE::sys()->getService(CoreServices::SECTION_REPOSITORY)->retrieveSection($per);
            $encounter->setParent($per);
		}
		
		$this->retrieveEncForm($encounter);
		$obsValues = $edb->getObservationsInEncounter($encounter->getEncounter(), $encounter->doIncludeDeletedObs());
        $encounter->setObsValues($obsValues);
        $encounter->setEncChanged(false);
        $encounter->setObsChanged(false);
        $encounter->setNew(false);
        $encounter->setRetrieved(true);
        return $encounter;
    }

    private function createNew($encounter) {
        $edb = CLE::db('encounter');
        $adb = CLE::db('audit');
        $patient = $encounter->getPatient();
        $patientId = is_array($patient) ? $patient['id'] : $patient;

		$encounter->setRevisionNo(1);
        
        if ($encounter->getParent()) {
            list($enc, $createdObs) = $edb->createChildEncounterWithTypeAndObservations($encounter->getParent()->getEncounter(), $encounter->getType(), $encounter->getObsValues(), $encounter->getBadge(), $encounter->getEncounter());
        } else {
            list($enc, $createdObs) = $edb->createEncounterWithTypeAndObservations($patientId, $encounter->getType(), $encounter->getObsValues(), $encounter->getEncounter());
        }
		$encounter->setEncounter($enc);
        $encounter->setNew(false);
		$adb->auditEncounter("create", $encounter->getEncounter(), $createdObs);

        $encounter->setEncChanged(false);
        $encounter->setObsChanged(false);
		$encounter->setStored(true);
		

    }

    protected function populateEncounter($encounter, $force = false) {
        $edb = CLE::db('encounter');
        $encs = array();
        if ($encounter->isNew()) {
            $encounter->setEncData("id",null);
            return;
        }

        if ($encounter->getId()) {
			$enc = $edb->getEncounter($encounter->getId());
			if(!$enc) {
				throw new \ClinicLE\Data\Exception\RecordNotFoundException("No record found with id ".$encounter->getId());
			}
            $encounter->setEncounter($enc);
            $encounter->setNew(false);
			return;
		}
		
        if ($encounter->getType() && $encounter->getSection()) {
            $sm = CLE::svc(CoreServices::SECTION_REPOSITORY);
            $sm->retrieveSection($encounter->getSection());
            $encs = $edb->getChildEncountersWithType($encounter->getParent()->getEncounter(), $encounter->getType(), $encounter->getBadge());
        } elseif ($encounter->getType() && $encounter->getPatient()) {
            $pid = $encounter->getPatient();
            $encs = $edb->getEncountersWithPatientAndType($pid, $encounter->getType());
        }
        if (!$encs || count($encs) == 0) {
            $encounter->setId(null);
            $encounter->setNew(true);
            return;
        }
		$encounter->setEncounter($encs[0]);
        $encounter->setNew(false);
        return $encs[0];
    }

    function deleteEncounter($encounter) {
		$this->populateEncounter($encounter);
        $encounter->setEncData('is_deleted', 1);
        $this->storeEncounter($encounter);
        CLE::db('audit')->auditEncounter("delete", $encounter->getEncounter(), array(), array());
    }

    function purgeEncounter($encounter) {
        if ($encounter->isNew()) {
            throw new ADD_EncounterException("Cannot purge record if it is a new record");
        }
        $edb = CLE::db('encounter');
        $edb->purgeRelatedEncounters($encounter->encounter);
        $encounter->setPurged(true);
        $encounter->setEncounter(null);
        $encounter->setId(null);
        $encounter->setObsValues(null);
        $encounter->setPatient(null);
    }

    public function storeEncounter($encounter) {
        if ($encounter->isPurged()) {
            throw new ADD_EncounterException("Cannot store record it has been purged");
        }
        $edb = CLE::db('encounter');
        $adb = CLE::db('audit');

        $auditType = "update";
        if ($encounter->isNew() || !$encounter->exists()) {
            $this->createNew($encounter);
            return;
        }

        if ($encounter->isObsChanged()) {
            $encounter->setEncData("data_hash", ADD_Util::hashData($encounter->getObsValues()));
        }
        $this->retrieveEncForm($encounter);
		
        if ($encounter->isEncChanged()) {
            $edb->updateEncounter($encounter->getEncounter());
        }
        $createdObs = null;
        $updatedObs = null;
		
        if ($encounter->isObsChanged()) {
            
            list($createdObs, $updatedObs) = $edb->updateEncounterObservations($encounter->getEncounter(), $encounter->getObsValues());

            $revNo = $encounter->getEncData('revision_no') ? $encounter->getEncData('revision_no') : 0;
            $revNo++;
            $encounter->setEncData('revision_no', $revNo);
        }
        $adb->auditEncounter($auditType, $encounter->getEncounter(), $createdObs, $updatedObs);

        $encounter->setEncChanged(false);
        $encounter->setObsChanged(false);
		$encounter->setStored(true);
		$encounter->setNew(false);
        return $this;
    }

    function countMatchingEncounters($encounter) {
        $add = CLE::sys();
        $edb = $add->db('encounter');
        if ($encounter->getId()) {
            return 1;
        }
        return $edb->countEncountersWithPatientAndType($encounter->getPatient(), $encounter->getType(), $encounter->getBadge());
    }
	function createQuery() {
		return new \ClinicLE\Data\Query\EavEncounterQuery();
	}
}
