<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Repository;

use ClinicLE\Data\Record\EncounterRecord as Encounter;
//use ADD;
use ADD_Util;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Common\Util;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\Data\Exception\PurgedRecordException;
use ClinicLE\Data\Exception\TableUndefinedException;
use ClinicLE\Data\Exception\RecordNotFoundException;
use ADD as Clinicle;
use ClinicLE\Data\Query\EncounterQuery;
use ClinicLE\Common\Connection\StandardConnection;

class TableEncounterRepository extends AbstractEncounterRepository {
    const CREATE="create";
    const UPDATE="update";
    
    public function __construct(StandardConnection $connection) {
        $this->conn = $connection;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function storeEncounter($encounter) {
        $this->preventMissingDetails($encounter);
        if (!$encounter->isNew()) {
            $this->updateEncounter($encounter);
        } else {
            $this->createEncounter($encounter);
        }
    }

    private function createEncounter($encounter) {
        $conn = $this->getConnection();
        $tableName = $this->getTableName($encounter);
        $encounter->setRevisionNo(1);
        $conn->insert($tableName, $encounter->getAllData(), array("date_created","date_updated"));

        $encounter->setId($conn->getLastInsertId());
        $encounter->setLastCreatedData($encounter->getObsValues());
        $encounter->setLastUpdatedData(array());
        
        $this->auditEncounter(self::CREATE, $encounter);
    }

    private function updateEncounter($encounter) {

        $conn = $this->getConnection();
        $tableName = $this->getTableName($encounter);

        $this->populateChangesToEncounter($encounter);

        if ($encounter->isObsChanged()) {
            $this->incrementRevisionNo($encounter);
        }
        $conn->update($tableName, $encounter->getId(), $encounter->getAllData(), array("date_updated"));
        
        if ($encounter->isObsChanged()) {
            $this->auditEncounter(self::UPDATE, $encounter);
        }
    }

    private function auditEncounter($action, $encounter) {
        $actionInfo = Util::serialize(array("created" => $encounter->getLastCreatedData(), "updated" => $encounter->getLastUpdatedData()));
        $user = Clinicle::sys()->getAccessManager()->getUser();
//echo "audit data:".$actionInfo."\n";
        $data = array(
            "action" => $action,
            "record_id" => $encounter->getId(),
            "record_type" => "encounter",
            "record_name" => $encounter->getType(),
            "action_info" => $actionInfo,
            "revision_no" => $encounter->getRevisionNo(),
            "user_id" => $user ? $user->username : "unknown"
        );
        $this->getConnection()->insert("audit", $data);
    }

    private function preventPurgedRecordBeingStored($encounter) {
        if ($encounter->isPurged()) {
            throw new PurgedRecordException("Purged encounter cannot be stored");
        }
    }

    private function preventRecordWithEmptyType($encounter) {
        if (Util::isEmpty($encounter->getType())) {
            throw new NullValueException("Encounter type cannot be empty or null");
        }
    }

    private function preventRecordWithNoID($encounter) {
        if (Util::isEmpty($encounter->getId())) {
            throw new NullValueException("Encounter ID cannot be empty or null");
        }
    }

    private function preventRecordWithUndefinedTable($encounter) {
        $tableName = $this->getTableName($encounter);

        if ($this->getConnection()->doesTableExist($tableName) == false) {
            throw new TableUndefinedException("Encounter table '$tableName' is undefined in the database");
        }
    }

    private function populateChangesToEncounter($encounter) {
        $oldEncounter = $this->getConnection()->selectById($this->getTableName($encounter), $encounter->getId());
        if (!$oldEncounter) {
            throw new RecordNotFoundException("Encounter with ID '" . $encounter->getId() . "' and type '" . $encounter->getType() . "' was not found so could not be updated");
        }
        $created = array();
        $updated = array();
        $changed = 0;

        foreach ($encounter->getObsValues() as $field => $value) {
            if (!isset($oldEncounter[$field])) {
                $created[$field] = $value;
                $changed++;
            } elseif ($oldEncounter[$field] != $encounter->getObsValue($field)) {
                $updated[$field] = $value;
                $changed++;
            }
        }
        $encounter->setLastCreatedData($created);
        $encounter->setLastUpdatedData($updated);

        if ($changed > 0) {
            $encounter->setObsChanged(true);
        }
    }

    public function getTableName($encounterOrType) {
        if (is_object($encounterOrType)) {
            $encounterOrType = $encounterOrType->getType();
        }
        return "e_" . $encounterOrType;
    }

    public function incrementRevisionNo($encounter) {
        $revisionNo = $encounter->getRevisionNo();
        $revisionNo++;
        $encounter->setRevisionNo($revisionNo);
    }

    /**
     * 
     * @param \Clinicle\Database\EncounterQuery $query
     * @return \Clinicle\Database\EncounterCollection
     */
    public function retrieveEncounter($encounter, $force = false) {
        if($encounter->isRetrieved()) {
            return;
        }
        
        if ($encounter->isNew()) {
            $encounter->setEncounter(null);
            $encounter->setId(null);

            return;
        }
        $query = new EncounterQuery();
            
        if ($encounter->getId()) {
			$query->filterById($encounter->getId());
            $query->filterByType($encounter->getType());
        } elseif ($encounter->getType() && $encounter->getSection()) {
            $sm = ADD::sys()->getSectionManager();
            $sm->retrieveSection($encounter->getSection());
            $query->filterBySectionId($encounter->getSection()->getId());
            $query->filterByType($encounter->getType());
        } elseif ($encounter->getType() && $encounter->getPatient()) {
            $query->filterByPatientId($encounter->getPatient());
            $query->filterByType($encounter->getType());
        }
        
        list($sql, $params) = $query->generateFullSQL();
        
        $encs = $this->getConnection()->fetchAll($sql, $params);
        if (!$encs || count($encs) == 0) {
            $encounter->setEncounter(null);
            $encounter->setId(null);
            $encounter->setNew(true);
            return;
        }
        $singleEnc = $encs[0];
        $encounter->setAllData($singleEnc);
        $encounter->setRetrieved(true);
    }
    
    public function deleteEncounter($encounter) {
        $this->preventMissingDetails($encounter);
        $this->preventRecordWithNoID($encounter);
        if ($encounter->isNew()) {
            throw new EncounterNotFoundException("New unsaved encounter with type '" . $encounter->getType() . "' can not be deleted");
        }
        if (!$encounter->exists()) {
            throw new EncounterNotFoundException("Encounter with ID '" . $encounter->getId() . "' and type '" . $encounter->getType() . "' does not exist so could not be deleted");
        }
        $encounter->setEncData("is_deleted",1);
        $this->storeEncounter($encounter);
    }

    private function preventMissingDetails($encounter) {
        $this->preventPurgedRecordBeingStored($encounter);
        $this->preventRecordWithEmptyType($encounter);
        $this->preventRecordWithUndefinedTable($encounter);
    }

    function incrementId($name) {
        $ir = $this->getConnection()->selectFirstByField("identifier", "name", $name);
        if (!$ir) {
            $irValue = 1;
            $ir = array("name" => $name, "value" => $irValue);
            $this->getConnection()->insert("identifier", $ir, array("date_created", "date_updated"));
        } else {
            $irValue = $ir['value'] + 1;
            $this->getConnection()->update("identifier", $ir, array("value" => $irValue), array("date_updated"));
        }
        return $irValue;
    }

    public function countMatchingEncounters($encounter) {
        
    }


    public function purgeEncounter($encounter) {
        
    }
	function createQuery() {
		return new \ClinicLE\Data\Query\TableEncounterQuery();
	}
}

