<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ObservationDictionary
 *
 * @author rcf8
 */
class ADD_Export_ObservationDictionary {
	private $_encounterLookup;
	private $_obsFields=array();
	private $_includeRecType=false;
	private $_concatMultiRecords=false;
	private $_useNamesOnly=false;
    private $_fieldValuesOnly=false;
    private $_sepCheckValues = false;
	function setObsFields($type,$obsFields) {
		$this->_obsFields[$type]=$obsFields;
	}
	function getObsFields($type) {
		return ADD_Util::val($type,$this->_obsFields);
	}
    function setUseNamesOnly($useNamesOnly) {
        $this->_useNamesOnly = $useNamesOnly;
    }
    function setFieldValuesOnly($fieldValuesOnly) {
        $this->_fieldValuesOnly = $fieldValuesOnly;
    }
    function setSepCheckValues($sepCheckValues) {
        $this->_sepCheckValues = $sepCheckValues;
    }
	public function __construct(ADD_Export_EncounterLookup $encounterLookup, $includeRecType) {
		$this->_encounterLookup = $encounterLookup;
		$this->_includeRecType=$includeRecType;
		$this->_concatMultiRecords=false;
	}
	public function convertToTitles(ADD_EncounterRecord $er, &$existingObsValues) {
		$page = $this->_encounterLookup->lookupPage($er->getType());
		
		if (!$page) return $er->getObsValues();
		
		if (!$page->hasComponent('Form')) return $er->getObsValues();
		$obsList = array();
		$form = $page->getComponent('Form');
		$form->setup($page);
		
		$obsValues = $er->getObsValues();
		$title=array($page->getSetting('title'));
		$ppage = $page->getParent();
		if ($ppage) {
			array_unshift($title,$ppage->getSetting('title'));
			$gppage = $ppage->getParent();
		
			if ($gppage) {
				array_unshift($title,$gppage->getSetting('title'));
			}
			$this->_baseType = $ppage->getSetting('encounter');
		}
		else {
			$this->_baseType = $er->getType();
		}
		$tps = $page->getModule()->getSuperModule()->getTopPages();
        $useNames = $this->_useNamesOnly;
        
		$modTitle = isset($tps[0]) ? $tps[0]->getSetting('title') : $page->getModule()->getName();
		$this->_recType = $useNames ? $page->getModule()->getAlias().":".$page->getName() : $modTitle.":".$page->getSetting('title');
		if (!$this->_includeRecType) $obsList['Record type']=$this->_recType;
		$page->setData($obsValues);
		$form->populate();
		
		foreach($obsValues as $obsName => $obsValue) {
			$field = $form->getField($obsName);
		  
			if(!$field) {
				$obsList[$obsName]=$obsValue;
				continue;
			}
			if(is_a($field,"ADD_Field_Memo") || $field->getSetting("no_export")) {
			    continue;
            }
			list($obsLabel,$suffix) = $this->getTableHeading($field,$existingObsValues, $er);
			
            if(is_a($field,"ADD_Field_List_MultiSelect") && $this->_sepCheckValues) {
                $field->finaliseField($page);
                //ADD::log($this)->info("field:".print_r($field,1));
                //exit;
                
                foreach($field->getOptions()->getOptionList() as $optValue => $optLabel) {
                    $obsList[$obsLabel." (".$optLabel.")".$suffix]=$field->hasValue($optValue) ? 1 : 0;
                }
                
                continue;
            }
			if ($this->_concatMultiRecords && isset($existingObsValues[$obsLabel])) {
				$existingObsValues[$obsLabel.$suffix].=",".$field->getDisplayText($page,null);
			}
			else {
				if(isset($obsList[$obsLabel.$suffix])) {
					ADD::web()->addWarning("Observation with label '$obsLabel' is repeated",true);
				}
                if($this->_fieldValuesOnly) {
                    $obsList[$obsLabel.$suffix]=$obsValue;
                }
                else {
    				$obsList[$obsLabel.$suffix]=$field->getDisplayText($page,null);
    				$obsList[$obsLabel.":value".$suffix]=$obsValue;
                }
			}
		}
		
		return $obsList;
	} 
	private function getTableHeading($field,$existingObsValues, $er) {
	    $useNames = $this->_useNamesOnly;
        
		$obsLabel = $useNames ? $field->getName() : strip_tags($field->getSetting('display_title',$field->getTitle())). " (".$field->getName().")";
		$suffix = "";
		if ($this->_includeRecType && !$this->_concatMultiRecords) {
			$suffix.=" [".$this->_recType."]";
			$count = $this->getUniqueEncCount($er);
			$suffix.=" ".$count;

		}
		return array($obsLabel,$suffix);	
	}
	private $encCounts = array();
	private function getUniqueEncCount($er) {
		$parentId = $er->getEncData("parent_encounter_id");
		$patientId = $er->getEncData("patient_id");
		
		if(!isset($this->encCounts[$this->_baseType])) {
			$this->encCounts[$this->_baseType]=array();
		}
		
		if(!isset($this->encCounts[$this->_baseType][$patientId])) {
			$this->encCounts[$this->_baseType][$patientId]=array();
		}
		
		if(!isset($this->encCounts[$this->_baseType][$patientId][$parentId])) {
			$this->encCounts[$this->_baseType][$patientId][$parentId]=count($this->encCounts[$this->_baseType][$patientId])+1;
		}
		return $this->encCounts[$this->_baseType][$patientId][$parentId];
	}
}