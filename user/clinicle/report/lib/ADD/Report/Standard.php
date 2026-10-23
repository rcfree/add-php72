<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Report_Standard extends ADD_Report_Base implements ADD_Form_FieldContainerInterface {
	protected $_fieldContent = null;
	function getContentClass() {
		return "Pdf";
	}	
	function initSpecific() {
		$this->_fieldContent = new ADD_Form_FieldContent();
		$rsect = $this->_reportConfig->getReportSection();
		$pathsToCheck = array($this->_reportConfig->getModule()->getPath());
		foreach($this->_reportConfig->getModule()->getPages() as $page) {
			$pathsToCheck[$page->getFilePath()]=true;
		}
		
		foreach(array_keys($pathsToCheck) as $pathToCheck) {
			$formPath = $pathToCheck."/report/".$rsect->getSetting('file').".".$rsect->getSetting("file_ext","xlsx");	
			if(!file_exists($formPath)) continue;
			$fileSetup = new ADD_Form_FileSetup($this);
			$fileSetup->loadAndInit($formPath, "ReportData");
			return;
		}
		throw new Exception("Report file '".$rsect->getSetting('file').".".$rsect->getSetting("file_ext","xlsx")."' not found in module or sub-module paths");
	}
	
	public function populate($pageLike) {
		$em = ADD::sys()->getEncounterManager();
           
		if($this->isEmptyReport()) return;
		$id = ADD::sys()->getParam('id');
		
		$this->_dataPopulate = new ADD_Report_DataPopulate(ADD::sys()->getPatientId(), $this->_reportConfig->getModule()->getName(), $id, $pageLike); 
		
		
		$form = $this->_fieldContent;
		
		$data = $this->_dataPopulate->populateDataUsingForm($form);

		$this->setData($data);
                
		$this->_populateFields($this);
	}
	
	//FOR COMPATIBILITY WITH LEGACY
	public function getObsCache($name) {
		return $this->_dataPopulate->getObsCache($name);
	}
	
	public function _populateFields($pageLike) {
		$data = $this->getData();
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->beforePopulateFields($pageLike);
		}
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->populateValue($this->getData(),$pageLike);
			if(method_exists($field,"removeData")) $field->removeData($this);
		}
		foreach($this->_fieldContent->getFieldsByName() as $name => $field) {
			$field->afterPopulateFields($pageLike);
		}
	
	}
	
	function generate() {
		$add = ADD::sys();
		$cont = $this->getContent();
		
		$topLevelFields = $this->_fieldContent->getTopLevelFields();
		$currentY = $cont->getCurrentY();
		$currentX = $cont->getCurrentX();
		$pdf = $cont->getPdf();
		$fieldCount = 0;
		foreach($topLevelFields as $fieldName) {
			$field = $this->getField($fieldName);
		
			if ($field->getSetting('with_next')) {
				$nextFieldName = $topLevelFields[$fieldCount+1];
				$nextField = $this->getField($nextFieldName);
				if ($nextField) {
					$nextHeight = $nextField->calcHeight($this);
					if (get_class($nextField)=="ADD_ReportField_ObsBlock") {
						$nextChildren = $this->getChildFields($nextFieldName);
						$nextHeight = isset($nextChildren[0]) ? $nextChildren[0]->calcHeight($this) : 0;
					}
					
					$currHeight = $field->calcHeight($this);
					$isAdded = $cont->checkPageBreak($this,$nextHeight + $currHeight,$currentX,$currentY);
				}
			}
			$fieldClass = get_class($field);
			if ($fieldClass=="ADD_ReportField_Obs" || $fieldClass=="ADD_ReportField_Markup" || $fieldClass=="ADD_ReportField_HContainer" || $fieldClass=="ADD_ReportField_RBox") {
				$heightOfNext = $field->calcHeight($this);
				$cont->checkPageBreak($this,$heightOfNext, $currentX,$currentY);
			}
			
			list($width,$height, $right, $bottom) = $field->createReportElement($this,$currentX,$currentY);
						
			$currentY = $bottom;
			$cont->setCurrentY($bottom);
			$cont->setCurrentX($right);
			$fieldCount++;
		}
	}
	
	public function isEmptyReport() {
		if (!$this->_reportConfig->isEmpty()) return;
		$this->setData(array());
		foreach($this->getFieldsByName() as $name => $field) {
			$title = $field->getTitle();
			$counts=0;
			$title = preg_replace('/\{.*\}/',"",$title,-1,$counts);
			if ($counts>0) $field->setTitle($title);
		}

		return true;
	}
	
	function setChildFieldsByName($childFieldsByName) {
		$this->_fieldContent->setChildFieldsByName($childFieldsByName);
	}

	function setParentFieldsByName($parentFieldsByName) {
		$this->_fieldContent->setParentFieldsByName($parentFieldsByName);
	}

	function getFields() {
		return $this->_fieldContent->getFields();
	}

	function getChildFieldsByName() {
		return $this->_fieldContent->getChildFieldsByName();
	}

	function getParentFieldsByName() {
		return $this->_fieldContent->getParentFieldsByName();
	}

	public function getParentField($name) {
		return $this->_fieldContent->getParentField($name);	
	}

	public function getChildFields($fieldName) {
		return $this->_fieldContent->getChildFields($fieldName);	
	}

	function getFieldsByName() {
		return $this->_fieldContent->getFieldsByName();	
	}

	function getField($fieldName) {
		return $this->_fieldContent->getField($fieldName);	
	}

	function addField($field) {
		return $this->_fieldContent->addField($field);	
	}
	
	function unshiftField($field) {
		return $this->_fieldContent->unshiftField($field);	
	}
	
	function areFieldsInit() {
		return $this->_fieldContent->areFieldsInit();
	}
	function setFieldsInit($fieldsInit) {
		return $this->_fieldContent->setFieldsInit($fieldsInit);
	}
	
	function prepareChildrenAndTopLevelFields() {
		return $this->_fieldContent->prepareChildrenAndTopLevelFields();
	}
	
	
	function addJavascript($javascript) {}
	function addWarning() {}
	function addMessage() {}
	
	function getPdf() { 
		return $this->getContent()->getPdf();
	}
	function checkPageBreak($heightOfNext, &$currentX,&$currentY) {
		return $this->getContent()->checkPageBreak($this,$heightOfNext, $currentX,$currentY);
	}
	function getCurrentY() {
		return $this->getContent()->getCurrentY();
	}
	function getCurrentX() {
		return $this->getContent()->getCurrentX();
	}

	public function addChildField($parentFieldName, $childField) {
		
	}

	public function getChildFieldAtPosition($position) {
		
	}

	public function getNextField($searchField) {
		
	}

	public function getTopLevelFields() {
		
	}
	
	public function getName() {
		$add = ADD::sys();
		return $this->_reportConfig->getModule()->getPage($add->getParam('page'))->getName();
	}
	
	
	public function setEncounter($encounter) {
		$this->_encounter = $encounter;
	}
	public function getEncounter() {
		return $this->_encounter;
	}
	
	public function doNotFinalise() {
		return false;
	}

	public function getComponent($name) {
		if ($name == "Form") {
			return $this;
		}
	}
	function getReportExtension() {
		return "pdf";
	}
}