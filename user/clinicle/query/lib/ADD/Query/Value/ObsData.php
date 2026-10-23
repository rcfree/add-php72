<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
;
class ADD_Query_Value_ObsData extends ADD_Query_Value {
	private $_encForm;
	private $_page;
	private $_noFields=false;
	private $_multiOptionFields=array();
	private $_excludeFields=array();
	
	function getPage() {
		return $this->_page;
	}
	function getEncType() {
		return $this->_encType;
	}
    function __construct($attrs,ADD_Query_Run $run) {
        parent::__construct($attrs);
        $this->_encType = $this->assertAttr("encounter");
        $this->_encForm = ADD::db('encounter')->getEncounterFormWithType($this->_encType);
		if(!$this->_encForm) throw new Exception("Form of type '".$this->_encType."' not found in ADD");
		
		if($this->getAttr("form_pim")) {
			$this->_page = ADD::sys()->getPageFromPim($this->getAttr("form_pim"));
		}
		else {
			$this->_page = $run->getEncounterLookup()->lookupPage($this->_encType);
		}
		if(!$this->_page) throw new Exception("Page with type ".$this->_encType." was not found in the system");
			
		$this->_obsFields = ADD::db('encounter')->getObservationFieldsInEncounterForm($this->_encForm['id']);
		if(count($this->_obsFields)==0) {
			$this->_noFields=true;
		}
		if(!$this->_fields) {
			$this->_fields=array_map(function($obsField) { return $obsField['name']; },$this->_obsFields);
		}
		$this->_page->setupComponents();
    }
    function addFieldsToResult(ADD_Query_Result $result) {
		if($this->_internal || $this->_noFields) return;
		if(count($this->_obsFields)==0) return;
		
		$page = $this->getPage();
		if(!$page->hasComponent('Form')) {
			return;
		}
		$form = $page->getComponent("Form");
		
		foreach($this->_obsFields as $obsField) {
			$obsFieldName = $obsField['name'];
			if(!in_array($obsFieldName,$this->_fields)) continue;
			$field = $form->getField($obsFieldName);
			if(!$field || !$field->isStored()) {
				$this->_excludeFields[$obsFieldName]=true;
				continue;
			}
			if(is_a($field,"ADD_Field_Container")) {
				$this->_excludeFields[$obsFieldName]=true;
				continue;
			}
			if(is_a($field,"ADD_Field_List_MultiSelect")) {
				$field->finaliseField($page);
				foreach($field->getOptions()->getOptionList() as $value => $label) {
					$this->_fieldMap[$obsFieldName.".".$value]=$field->getSetting('display_title',$obsField['title'])." [".$label."]";
					$this->_multiOptionFields[$obsFieldName][$value]=$value;
				}
				
				continue;
			}
			$this->_fieldMap[$obsFieldName]=$field->getSetting('display_title',$obsField['title']);
		}
		$result->addEncounterFieldMap($this->_encType,$this->_fieldMap);
	}
    function retrieveDataForPatient(ADD_Query_ResultItem $resultItem, ADD_Query_Run $run) {
    	if($this->_noFields) return;
    	$qdb = ADD::db('query');
		$rows = $qdb->fetchObservationData($this->_fields,$this->_encForm,$resultItem,false);
		
		$fieldData = $this->_convertRowsIntoFieldData($rows);
		$resultItem->addDataFromFieldData($fieldData);
    }
	
	function _convertRowsIntoFieldData($rows) {
		if(!$rows) return array();
		$log = ADD::log($this);
		$encType = $this->getEncType();
		$idField = "encounter_id";
		$fieldData = array();
		if ($this->getAttr("raw_values")) {
			foreach($rows as $row) {
				$idValue = $row[$idField];
				$name = $row['name'];
				$value = $row['value'];
				$fieldData[$encType][$idValue][$name]=$value;
			}
			return $fieldData;
		}
		
		$page = $this->getPage();
		if(!$page) throw new Exception("Not able to get form data for encounter type '$encType'");

		if(!$page->hasComponent('Form')) {
			 throw new Exception("Form for '".$encType."' was not found in page:".$page->getName().". Could use 'raw_values' attribute to ignore this.");
			return;
		}
		$form = $page->getComponent("Form");
		
		foreach($rows as $row) {
			$idValue = $row[$idField];
			$name = $row['name'];
			$value = $row['value'];
			if(isset($this->_excludeFields[$name])) {
				continue;
			}
			
			if($row['is_serialized']) $value=ADD_Util::unserialize($value);
			$field = $form->getField($name);
			if(!$field) throw new Exception("Field '$name' was not found in page:".$page->getName());
			$field->populateValue(array($name=>$value),$page);
			
			if(isset($this->_multiOptionFields[$name])) {
				foreach($this->_multiOptionFields[$name] as $value) {
					$fieldData[$encType][$idValue][$name.".".$value]=$field->hasValue($value) ? 1 : 0;
				}
			}
			else {
				$displayText = $field->getDisplayText($page,null);
				if(strlen($value)>0 && ADD_Util::isEmpty($displayText)) {
					throw new Exception("Field '".$name."' has empty label for value '$value':".print_r($field,1));
				}
				$fieldData[$encType][$idValue][$name]=$displayText;
			}
		}
		return $fieldData;
	}
}
