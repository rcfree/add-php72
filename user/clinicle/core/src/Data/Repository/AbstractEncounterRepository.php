<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Data\Repository;

use ClinicLE\Data\Exception\RecordNotFoundException;
use ClinicLE\Data\Record\EncounterRecord;
use ADD;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\Common\Connection\StandardConnection;

/**
 * Description of EncounterRepository
 *
 * @author rob
 */
abstract class AbstractEncounterRepository {

    private $obsFields = array();
	
	
    public function __construct(StandardConnection $connection) {
        $this->conn = $connection;
    }

    public function getConnection() {
        return $this->conn;
    }

    abstract function retrieveEncounter($encounter, $force = false);

    
    abstract function deleteEncounter($encounter);

    abstract function purgeEncounter($encounter);

    abstract function storeEncounter($encounter);

    function getObsFields($type) {
        if (!$type) {
            throw new ADD_EncounterException("No form type provided to EncounterRecord");
        }
        if (!isset($this->obsFields[$type])) {
            $edb = ADD::db('encounter');
            $encForm = $edb->getEncounterFormWithType($type);
            $fields = $edb->getObservationFieldsByIdAndName($encForm['id']);
            $this->obsFields[$type] = $fields;
        }
        return $this->obsFields[$type];
    }

    abstract function countMatchingEncounters($encounter);

    function getFieldObsText(EncounterRecord $encounter, $obsName, $page) {
        if (!$page)
            return $encounter->getObsValue($obsName);

        if (!$page->hasComponent('Form')) {
            return $encounter->getObsValue($obsName);
        }

        $form = $page->getComponent('Form');
        $form->setup($page);

        $field = $form->getField($obsName);
        if (!is_a($field, "ADD_Field_List")) {
            return $encounter->getObsValue($obsName);
        }
        if (!$field) {
            ADD::log($this)->warn("field $obsName not found");
            return null;
        }
        $field->setValue($encounter->getObsValue($obsName));

        return $field->getDisplayText($page, null);
    }

    function retrieveEncForm($encounter) {
        $edb = ADD::db('encounter');
        
        if($encounter->getEncData("encounter_form_id")) {
            $encForm = $edb->getEncounterForm($encounter->getEncData("encounter_form_id"));
            if(!$encForm) {
                throw new RecordNotFoundException("Encounter form with ID '{$encounter->getEncData("encounter_form_id")}' is not defined in the database");
            }
            return $encForm;
        }
	if (!$encounter -> getType()) {
            throw new NullValueException("No encounter type or encounter form ID in encounter record");
        }
	$encForm = $edb -> getEncounterFormWithType($encounter -> getType());
        if(!$encForm) {
            throw new RecordNotFoundException("Encounter form with type '{$encounter->getType()}' is not defined in the database");
        }
		$encounter->setEncData("encounter_form_id",$encForm['id']);
        return $encForm;
    }

    /**
     * @return \ClinicLE\Data\Query\AbstractEncounterQuery
     */
	abstract function createQuery();
}
