<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Report_DataPopulate {
	private $_data = array();
	private $_id;
	private $_patientId;
	private $_moduleName;
	private $_currentPage;
	private $_obsCache = array();

	public function __construct($patientId, $moduleName, $id, $currentPage, $fieldDelimiters=array("{","}"),$valuesAsLabels=false) {
		$this->_patientId = $patientId;
		$this->_id = $id;
		$this->_moduleName = $moduleName;
		$this->_currentPage = $currentPage;
		$this->_fieldDelLeft=$fieldDelimiters[0];
		$this->_fieldDelRight=$fieldDelimiters[1];
		$this->_valuesAsLabels=$valuesAsLabels;
		
		$this->_currentPage->getComponent('Encounter')->reset();

        $this->_currentRecord = $this->_currentPage->getEncounterRecord($this->_id);
		
		if (!$this->_currentRecord->exists()) throw new ADD_ReportException("Encounter record with ID '".$this->_id."' was not found");

		$this->_currentRecord->retrieve();  

	}
	
	function getObsCache($sectionName) {
		if (isset($this->_obsCache[$sectionName])) {
			return $this->_obsCache[$sectionName];
		}
	}
	protected function setObsCache($sectionName,$obs) {
		$this->_obsCache[$sectionName]=$obs;
	}
	
	public function getData() {
		return $this->_data;
	}
	public function append(array $appendData=null) {
		if(!$appendData) return;
		$this->_data = $this->_data + $appendData;
	}
	public function populateDataUsingNames(array $names) {
		$this->appendPatientData();
		
		$this->appendCurrentEncounterData();
		
		foreach($names as $name) {
			if($this->_appendFieldsInFunction($name)) continue;
			if($this->_isDemographicsData($name)) continue;
			$this->appendDataForRelatedField($name);
		}
		$this->appendReportData();
        return $this->_data;
	}
	
	public function populateDataUsingForm($form) {
		$dataFields = array();
		foreach($form->getFields() as $field) {
			$markupFields = $this->_findFieldInMarkup($field);
			
			if($markupFields) {
				$dataFields = array_merge($dataFields,$markupFields);
			}
			else {
				$dataFields[]=$field->getName();
			}
		}
		$this->appendReportData();
		$this->populateDataUsingNames($dataFields);
		$this->_replacePlaceholdersInTitles($form);
		
		return $this->_data;
	}
	
	public function _replacePlaceholdersInTitles($form) {
		foreach($form->getFieldsByName() as $name => $field) {
			$title = $field->getTitle();
			if (!$field->getSetting('markup_fields')) continue;
			foreach($field->getSetting('markup_fields') as $fieldMatch) {
				$title = str_replace($this->_fieldDelLeft.$fieldMatch.$this->_fieldDelRight,$this->getDataItem($fieldMatch),$title);
			}
			$field->setTitle($title);
		}
	}
	
	public function getDataItem($key) {
		return ADD_Util::val($key,$this->_data);
	}
	public function _findFieldInMarkup($field) {
		if ($field->getType()!="Markup") return null;
		$matches = array();
		$matchString = "/".preg_quote($this->_fieldDelLeft)."(.*?)".preg_quote($this->_fieldDelRight)."/";
		if (!preg_match_all($matchString,$field->getTitle(),$matches)) return null;
			
		$names=array();
		foreach($matches[1] as $singleMatch) {
			$names[]=$singleMatch;
		}
		$field->setSetting('markup_fields',$names);
		
		return $names;
	}
	
	public function appendPatientData() {
		$pdb = ADD::db('patient');
		
		list($patient,$address,$gp) = $pdb->getPatientDemographics($this->_patientId);
		$patData = array();
		if ($patient) {
			foreach($patient as $fieldName => $fieldData) {
				$patData['patient.'.$fieldName]=$fieldData;
			}
		}
		if ($address) {
			foreach($address as $fieldName => $fieldData) {
				$patData['address.'.$fieldName]=$fieldData;
			}
		}
		if ($gp) {
			foreach($gp as $fieldName => $fieldData) {
				$patData['gp.'.$fieldName]=$fieldData;
			}
		}
		
		$this->append($patData);
	}
	
	public function appendCurrentEncounterData() {
		$encData = $this->_currentRecord->getObsValues();
		$this->append($encData);
	}
	
	public function appendReportData() {
		$data = array();
		$data['report.date']=ADD_Field_Util::today();
		$user = ADD::sys()->getAccessManager()->getUser();
		$data['report.printed_by']=$user->first_name." ".$user->last_name;
		$this->append($data);
	}
	
	private function _isDemographicsData($name) {
		if (preg_match("/^patient\./",$name) || preg_match("/^address\./",$name) || preg_match("/^gp\./",$name) || preg_match("/^report\./",$name)) return true;
		return false;
	}
	
	private function _appendFieldsInFunction($name) {
		if(preg_match("/([\w]+)\('(.+)'/",$name,$matches)) {
			$function = $matches[1];
			$fields = explode(";",$matches[2]);
			foreach($fields as $field) {
				if($this->_isDemographicsData($field)) continue;
				$this->appendDataForRelatedField($field);
			}
			$this->_appendFunctionData($name);
			return true;
		}
		return false;
	}
	
	public function appendDataForRelatedField($name) {
		$module = ADD::sys()->getModule($this->_moduleName);
		$em = ADD::sys()->getEncounterManager();
		$log = ADD::log($this);
		$fieldBits = explode(".",$name);
		if (count($fieldBits)>1) {
			$spage = null;
			$fromParent = $fieldBits[0]=='parent';

			if ($fromParent) {
				$spage = $page = $module->getPage($fieldBits[1]);
				$fieldName = $fieldBits[2];
				if(!$spage) throw new Exception("Section page '".$fieldBits[1]."' not found");
			
			}
			else {
				$spage = $module->getPage($fieldBits[0]);
				if(!$spage) throw new Exception("Section page '".$fieldBits[0]."' not found");
			
				$fieldName = $fieldBits[1];
			}
			
			$record = $this->getObsCache($spage->getName());
			
			if (!$record) {
				if($fromParent) {
					$record = $em->child($this->_currentRecord->getParent(),$spage->getSetting('encounter'));
				}
				else {
					CLE::info("child",$this->_currentRecord);
					$record = $em->child($this->_currentRecord,$spage->getSetting('encounter'));
				}
				
				$record->retrieve();
				$this->setObsCache($spage->getName(),$record);
			}
			
			$this->_data[$name]=$this->_valuesAsLabels ? $record->getObsText($fieldName,$spage) : $record->getObsValue($fieldName);
			
			if ($this->_valuesAsLabels) {
				if(!isset($this->_data['fieldval'])) $this->_data['fieldval']=array();
				$this->_data['fieldval'][$name] = $record->getObsValue($fieldName);
			}
		}
	}
	
	public function _appendFunctionData($fieldFunction) {
		$eval = new ADD_Tool_Evaluate($fieldFunction);
		ADD_Report_Function::$values=$this->_data;
		$value = $eval->evaluate("ADD_Report_Function");
		$this->_data[$fieldFunction]=$value;
	}
	
}