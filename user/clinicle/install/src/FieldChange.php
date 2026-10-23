<?php

namespace ClinicLE\Install;
use ClinicLE\Install\Exception\FieldDefinitionException;
use ADD;
use ADD_Util;

/*
 * Here comes the text of your license
 * Each line should be prefixed with  * 
 */

/**
 * Description of DataSync
 *
 * @author rcf8
 */
class FieldChange {
    function __construct($field, $formSync) {
        $this->_field =$field;
        $this->_formSync = $formSync;
    }
    
    function alreadyDeleted() {
    	$obsField = $this->_formSync->getObsField($this->_field->getName());

        if ($obsField && $obsField['is_deleted'] == 1) {
            	return true;
        } 
        if (!$obsField && $this->_field->getLevel() == "DEL") return true;
    }
	
    function isCreated() {
        if (!$this->_formSync->getObsField($this->_field->getName())) return true;
    }
    function isUndeleted() {
        $obsField = $this->_formSync->getObsField($this->_field->getName());
        if($obsField && $obsField['is_deleted'] && $this->_field->getLevel()!="DEL") {
            return true;
        }
        return false;
    }
    function isChanged() {
    	
        $obsField = $this->_formSync->getObsField($this->_field->getName());
        	$fieldChanged=false;
            if (!$obsField || $obsField['is_deleted'])
                return false;
            if ($this->_field -> getSettings() != $obsField['settings']) {
				ADD::log()->debug("FIELD CHANGE '".$this->_field->getName()."' - settings from ".json_encode($obsField['settings'])." to ".json_encode($this->_field->getSettings()));
				$fieldChanged = true;
            }
            if ($this->_field -> getTitle() != $obsField['title']) {
                ADD::log()->debug("FIELD CHANGE '".$this->_field->getName()."' - title from ".$this->_field->getTitle()." to ".$obsField['title']);
				$fieldChanged = true;
            }
			
			$oldOptions = ADD_Util::getCsvValues($obsField['options']);
			$newOptions = $this->_field->getFieldData('Options');
			
			if(isset($newOptions[0])) {
				ADD::log()->warn("FIELD '".$this->_field->getName()."' has zero-indexed options");
			}
			if(substr($obsField['options_raw'],0,1)=="[") {
				ADD::log()->warn("FIELD CHANGE '".$this->_field->getName()."' - flat-list options will be updated from ".ADD_Util::serialize($oldOptions)." to ".ADD_Util::serialize($newOptions));
				$fieldChanged =true;
			}
			else {
				if ($newOptions != $oldOptions) {
					ADD::log()->debug("FIELD CHANGE '".$this->_field->getName()."' - options from ".json_encode($oldOptions)." to ".json_encode($newOptions));
					$fieldChanged = true;
				}
			}
				
            if ($this->_field -> getType() != $obsField['type']) {
                ADD::log()->info("FIELD CHANGE '".$this->_field->getName()."' - type from ".$obsField['type']. " to ".$this->_field -> getType());
                $fieldChanged = true;
            }
            if ($this->_field -> getLevel() != $obsField['level']) {
                ADD::log()->info("FIELD CHANGE '".$this->_field->getName()."' - level from ".$obsField['level'] ." to ".$this->_field->getLevel());
                $fieldChanged = true;
            }
            if ($this->_field -> getDefault() != $obsField['default_value']) {
                ADD::log()->info("FIELD CHANGE '".$this->_field->getName()."' - default from ".$obsField['default_value']. " to ".$this->_field -> getDefault() );
                $fieldChanged = true;
            }
            return $fieldChanged;
	}
    
    function isDeleted() {
        $log = ADD::log($this);
        return $this->_field -> getFieldData('Level') == "DEL" ? true : false;
        if (!$delete) return;
       
        return true;
    }
	function isAlreadyDeleted() {
		$obsField = $this->_formSync->getObsField($this->_field->getName());

        if ($obsField) {
            if ($obsField['is_deleted'] == 1) {
                    $log -> warn("DELETE FIELD '" . $this->_field -> getName() . "' has already been deleted");
                    return true;
            }
        } 
	}
    function exists() {
        return $this->_formSync->getObsField($this->_field->getName()) ? true : false;
    }
        function isRenamed() {
		$add = ADD::sys();
		$origName = $this->_field -> getFieldData('Rename');
		$log = ADD::log($this);
		if ($this->_field -> getFieldData('Level') == "DEL") return false;
		if (!$origName) return false;
                
                $origField = $this->_formSync->getObsField($origName);
                $currName = $this->_field->getName();
                
                //if orig field then rename it
		if ($origField) {
                    if ($origField['is_deleted']) return false;

                    if ($this->_formSync->getObsField($currName)) {
                            throw new FieldDefinitionException("Field '". $origName."' cannot be renamed to '" . $currName . "', the field already exists");
                    }
                    return true;
		}
                else {
                //if no orig field then check for present of renamed field
                    $renamedField = $this->_formSync->getObsField($this->_field->getName());
                    $log -> warn("RENAME FIELD '" . $currName . "' has already been renamed from '".$origName."'");
                   
                    return false;
		}
	}
	function isMoved() {
	    $obsField = $this->_formSync->getObsField($this->_field->getName());
	    if ($obsField['weight'] != $this->_field -> getWeight()) {
	        ADD::log($this) -> info("MOVE FIELD '" . $this->_field->getName() . "' " . $obsField['weight'] . "!=" . $this->_field -> getWeight());
	        return true;
	    }
		return false;
	}
	
	function confirmDependencies() {
		$dictName = $this->_field->getSetting('dictionary');
		if (!$dictName) return;
		$dict = ADD::db('dictionary')->getDictionaryByName($dictName);
		if(!$dict) throw new FieldDefinitionException("Dictionary '$dictName' defined in field '".$this->_field->getName()."' does not exist");
	}
}
?>