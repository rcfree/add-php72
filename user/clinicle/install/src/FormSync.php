<?php

namespace ClinicLE\Install;

use ADD;
use ADD_FieldFactory_Database;
/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of DataSync
 *
 * @author rcf8
 */
class FormSync {
    private $_formFile = null;
    private $_obsFields = array();
    private $_encForm = null;
    private $_created = array();
    private $_deleted = array();
    private $_updated = array();
    private $_purged = array();
    private $_renamed = array();
	private $_moved = array();
	
    private $_new = false;
    private $deleteMissing = false;
	
    function setNew($new) {
        $this->_new = $new;
    }
    function isNew() {
        return $this->_new;
    }
    function setObsFields($obsFields) {
        $this->_obsFields = $obsFields;
    }
    function setEncForm($encForm) {
        $this->_encForm = $encForm;
    }
    function getEncForm() {
        return $this->_encForm;
    }
    function __construct($formFile,$page) {
        $this->_formFile = $formFile;
        $this->_page = $page;
    }
    function getFormFile() {
        return $this->_formFile;
    }
    function setCreated($created) {
        $this->_created = $created;
    }
    function setRenamed($renamed) {
        $this->_renamed = $renamed;
    }
    function setUpdated($updated) {
        $this->_updated = $updated;
    }
    function setDeleted($deleted) {
        $this->_deleted = $deleted;
    }
    function setPurged($purged) {
        $this->_purged = $purged;
    }
	function setMoved($moved) {
        $this->_moved = $moved;
    }
    function getCreated() {
        return $this->_created;
    }
    function getRenamed() {
        return $this->_renamed;
    }
    function getUpdated() {
        return $this->_updated;
    }
    function getDeleted() {
        return $this->_deleted;
    }
    function getPurged() {
        return $this->_purged;
    }
	function getMoved() {
		return $this->_moved;
	}
    function setAudit($audit) {
        $this->_audit = $audit;
    }
    function getAudit() {
        return $this->_audit;
    }
    function getPage() {
        return $this->_page;
    }
    function totalChanges() {
        $changes = count($this->_created) + count($this->_updated) + count($this->_renamed) + count($this->_deleted);
        return $changes;
    }
    function getObsField($name) {
        return isset($this->_obsFields[$name]) ? $this->_obsFields[$name] : null;
    }
        
    function checkMissingFields() {
            $form = $this->getFormFile();
            $add = ADD::sys();
            $log = ADD::log($this);

            $fieldsByName = $form -> getFieldsByName();
            $renamed = $this->_renamed;
            $deleted = $this->_deleted;
            $purged = $this->_purged;
			$ff = $add->getFactory(\ClinicLE\System\Factory\SystemFactories::DB_FIELD);
			$missingFields = array();
            foreach ($this->_obsFields as $name => $obsField) {
                    if (isset($renamed[$name]))
                            continue;
                    if (isset($deleted[$name]))
                            continue;
                    if ($obsField['is_deleted'])
                            continue;
                    if (!isset($fieldsByName[$name])) {
                        $fieldObj = $ff->newInstance($obsField);
                        if (!$fieldObj -> isStored()) {
                                $purged[] = $obsField;
                                $log -> info("Missing non-data field '$name' purged automatically");
								continue;
						}
						if($this->doDeleteMissing()) {
							$this->_deleted[]=$obsField;
							$log -> info("Force delete missing field '$name'");
                            continue;
                        }

                        $missingFields[] = $name;
                    }
            }
            $this->setPurged($purged);
            return $missingFields;
    }
	function setDeleteMissing($deleteMissing) {
		$this->deleteMissing = $deleteMissing;
	}
	function doDeleteMissing() {
		return $this->deleteMissing;
	}
}
