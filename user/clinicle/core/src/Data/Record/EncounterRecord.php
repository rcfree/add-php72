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
use ClinicLE\System\Service\CoreServices;
use ADD;
use ADD_Util;

class EncounterRecord extends AbstractRecord {
	const SECTION_FIELD="parent_encounter_id";
	
    static private $encFields = array("id", "patient_id", self::SECTION_FIELD, "is_deleted", "badge",
        "is_complete",
        "is_locked",
        "is_invalid",
        "is_autosave",
        "is_verified",
        "revision_no",
        "date_created",
        "date_updated",
		"encounter_form_id",
		"data_hash"
    );

	private $obsValues = array();
	private $obsChanged = false;
    private $includeDeletedObs=false;
	private $lastCreatedData;
	private $lastUpdatedData;
    private $section;
	
	function setIncludeDeletedObs($includeDeletedObs) {
		$this->includeDeletedObs=$includeDeletedObs;
	}
	
	function retrieve($force=false) {
            ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->retrieveEncounter($this,$force);
            return $this;
	}

	function getParent() {
		return $this -> getSection();
	}
        
	function getSection() {
		return $this->section;
	}
	function getObsValue($name) {
		return isset($this -> obsValues[$name]) ? $this -> obsValues[$name] : false;
	}

	function setObsValue($name, $value) {
            if ($value===null) {
                $value="";
            }
            if(!is_array($value) && preg_match("/^[\[\{]/",$value)) {
				$value = ADD_Util::unserialize($value);
			}
            $prevValue = $this->getObsValue($name);
			
			if ($prevValue===$value && $prevValue!==false) {
                return false;
            }
            
            $this->obsValues[$name]=$value;
            $this->setObsChanged(true);               
	}

	function setObsValues($obsValues, $ignoreInvalids=false) {
		foreach ($obsValues as $name => $value) {
			$this->setObsValue($name,$value);
		}
	}

	function getObsValues($obsFields=null) {
		if (!$obsFields) return $this -> obsValues;
		$obsValues = array();
		foreach($obsFields as $obsField) {
			$obsValues[$obsField]=$this->getObsValue($obsField);
		}
		return $obsValues;
	}

	function getEncForm() {
                return ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->retrieveEncForm($this);
	}
        
        function getObsField($name) {
            $fields = $this->getObsFields($this->type);
            return isset($fields[$name]) ? $fields[$name] : null;
        }
	
	function audit($action) {
		ADD::db('audit') -> auditEncounter($action, $this -> encounter);
	}
        
	public function store($obsValues = null) {
            if($obsValues) {
                $this->setObsValues($obsValues);
            }
            ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->storeEncounter($this);
            return $this;
	}
    
	function isChanged() {
		return $this -> obsChanged;
	}
	function delete() {
            ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->deleteEncounter($this);
	}
	function purge() {
            ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->purgeEncounter($this);
	}

	function count($encounter) {
            return ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->countMatchingEncounters($encounter);
		
	}
	
	function getObsText($obsName, \ADD_Page $page) {
            return ADD::sys()->getService(CoreServices::ENCOUNTER_REPOSITORY)->getFieldObsText($this,$obsName,$page);
	}
        
        function doIncludeDeletedObs() {
            return $this->includeDeletedObs;
        }
        function setEncChanged($encChanged) {
            $this->encChanged = $encChanged;
        }

        function setObsChanged($obsChanged) {
            $this->obsChanged = $obsChanged;
        }
        function isObsChanged() {
            return $this->obsChanged;
        }
        function getRevisionNo() {
            return $this->getEncData("revision_no");
        }
        function setRevisionNo($revisionNo) {
            return $this->setEncData("revision_no",$revisionNo);
        }
        function getAllData() {
            return array_merge($this->getEncounter(),$this->getObsValues());
        }
        function setAllData($allData) {
            foreach($allData as $name => $value) {
                if(in_array($name,self::$encFields)) {
                    $this->setEncData($name,$value);
                }
                else {
                    $this->setObsValue($name,$value);
                }
                
            }
            
            $this->setEncChanged(false);
            $this->setObsChanged(false);
        }
        public function setLastCreatedData($createdData) {
            $this->lastCreatedData = $createdData;
        }
        public function setLastUpdatedData($updatedData) {
            $this->lastUpdatedData = $updatedData;
        }

        public function getLastCreatedData() {
            return $this->lastCreatedData;
        }
        public function getLastUpdatedData() {
            return $this->lastUpdatedData;
        }
        function setSection($section) {
			if(!$section) {
				$section = null;
				return;
			}
            $this->section = $section;
            $this->encounter[EncounterRecord::SECTION_FIELD] = $section->getId();
			if(!$this->getPatient() && $section->getPatient()) {
				$this->encounter["patient_id"]=$section->getPatient();
			}
			$this->encChanged=true;
            return $this;
        }
        function setParent($parent) {
            $this->setSection($parent);
        }
      function setBadge($badge) {
        $this->setEncData("badge",$badge);
    }

    function getBadge() {
        return $this->getEncData("badge");
    }

    function setNew($new) {
        parent::setNew($new);
        if(!$new) {
            return;
        }
        $this->setEncData("revision_no",1);
    }

}
