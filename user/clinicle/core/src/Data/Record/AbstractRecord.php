<?php

/**
 * EncounterRecord makes it easier to access and manage encounter record data in the database.
 * 
 * The EncounterRecord makes it provides a wrapper for DB functions which create, edit, delete etc. encounters and their associated observation data.
 * It is used alongside the EncounterManager to create and retrieve child encounter records.
 * The preferred approach is to use EncounterManager to create EncounterRecord instances.
 * 
 * @package Main
 * @example $em = new EncounterManager();
 * @example $er = $em->record();
 * @example $er->setId(3);
 * @example if ($er->exists()) $er->store(array("test"=>"1"));
 * @version 2.00
 * @since 14/03/2014
 * @author Rob Free
 */

namespace ClinicLE\Data\Record;

use ADD_EncounterRecord;

abstract class AbstractRecord extends ADD_EncounterRecord {

    const MULTI = "MULTI";

    protected $type = null;
    protected $encounter = null;
    protected $new = false;
    protected $purged = false;
    protected $encChanged = false;
    protected $retrieved = false;
    protected $stored = false;
	
    function isNew() {
        return $this->new;
    }

    function setNew($new) {
        $this->encChanged = true;
        $this->new = $new;
    }

    function exists() {
        $this->retrieve();
        return $this->getId() ? true : false;
    }

    function isStored() {
        return $this->stored;
    }

    abstract function retrieve($force = false);

    function setEncData($field, $value) {
        if($field=="section_id" || $field=="parent_encounter_id") {
                $sr = new SectionRecord();
                $sr->setEncounter(array("patient_id"=>$this->getPatient(),"id"=>$value));
                $this->setParent($sr);    
        }
        $this->encounter[$field] = $value;
        $this->encChanged = true;
    }

    function getEncData($field = null) {
        if (!$field) {
            return $this->encounter;
        }
        return isset($this->encounter[$field]) ? $this->encounter[$field] : null;
    }

    function getParent() {
        return $this->_parent;
    }

    function getSection() {
        return $this->_parent;
    }

    function setId($id) {
        $this->setEncData("id",$id);
        return $this;
    }

    function getType() {
        return $this->type;
    }

    function setType($type) {
        $this->encChanged = true;
        $this->type = $type;
        return $this;
    }

    function getEncounter() {
        return $this->encounter;
    }

    function getId() {
        return $this->getEncData("id");
    }

    function getPatient() {
        return $this->getEncData("patient_id");
    }

    function setPatient($patient) {
		if (is_array($patient)) {
            $patient = $patient['id'];
        }
		
        $this->setEncData("patient_id",$patient);
        return $this;
    }

    function setEncounter($encounter) {
        $this->encounter = $encounter;
        $this->encChanged = true;
        return $this;
    }

    abstract function audit($action);

    abstract function store($obsValues = null);

    function isChanged() {
        return $this->encChanged;
    }

    abstract function delete();

    abstract function purge();

    function isRetrieved() {
        return $this->retrieved;
    }

    function setRetrieved($retrieved) {
        $this->retrieved = $retrieved;
    }

    function setEncChanged($encChanged) {
        $this->encChanged = $encChanged;
    }

    function isPurged() {
        return $this->purged;
    }
    
    function isEncChanged() {
        return $this->encChanged;
    }
	
	function setStored($stored) {
		$this->stored = $stored;
	}
}
