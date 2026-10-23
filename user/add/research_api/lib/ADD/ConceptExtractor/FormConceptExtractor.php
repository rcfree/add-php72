<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_ConceptExtractor_FormConceptExtractor {
	protected $page;
	private $path="";
	private $tabLevel=null;
	private $pim;
	
	function __construct(ADD_Page $page) {
		$this->page = $page;
		$form = $this->page->getComponent('Form');
		$form->setup();
		$this->form = $form;
		$this->pim = $page->getModule()->getAlias().":".$page->getName();
	}

	function createConcepts() {
		$concepts = array();
		
		foreach($this->form->getFields() as $field) {
			if($field->getType()=="Tab") {
				$this->tabLevel = $field->getLevel();
				$conceptData=$this->getConceptData($field);
				$conceptData['path']=$field->getTitle()."\\";
				$concepts[]=$conceptData;
				$this->path = $field->getTitle();
				continue;
			}
			if(!$this->isFieldStored($field)) continue;
			if($this->tabLevel==$field->getLevel()) {
				$this->tabLevel=null;
				$this->path="";
			}
			$conceptData = $this->getConceptData($field);
			$concepts[]=$conceptData;
		}
		return $concepts;
	}
	
	protected function isFieldStored($field) {
		if(!$field->isStored()) {
			ADD::web()->addWarning("field isStored:".$field->getName().", false");
			return false;
		}
		if($field->getSetting('no_store')) {
			ADD::web()->addWarning("field no_store:".$field->getName().", false");		
			return false;
		}
		return true;
	}
	
	protected function createPath($field) {
		return ($this->path ? $this->path."\\" : "").$field->getName()."\\";
	}
	protected function getConceptData($field) {
		$fieldData=array(
			"name"=>$field->getName(),
			"title"=>$field->getSetting('display_title',strip_tags($field->getTitle())),
			"type"=>$this->createType($field),
			"path"=>$this->createPath($field),
			"code"=>$this->createCode($field)
		);
		$this->updateFieldData($field, $fieldData);
		
		return $fieldData;
	}
	
	protected function updateFieldData($field,&$fieldData) {
		if($field->getSetting('suffix')) {
			$fieldData['suffix']=strip_tags($field->getSetting('suffix'));
		}
		
		if(is_a($field,"ADD_Field_Container_AutoComplete")) {
			$fieldData['retrieve_options']=1;
		}
		if(is_a($field,"ADD_Field_List")) {
			$fieldData['options']=$this->createOptions($field);
		}		
	}
	protected function createOptions($field) {
		$options = $field->getOptions();
		//$optionCount = $options->countOptions();
		$optionList = $options->getOptionList();
		if(isset($optionList[""])) {
			unset($optionList[""]);
		}
		return $optionList;
	}
	protected function createCode($field) {
		return $this->pim.":".$field->getName();
	}
	
	protected function createType($field) {
		if($field->getType()=="Tab") {
			return "container";
		}
		if($field->getSetting('data_type')) {
			return $field->getSetting('data_type');
		}
		if(is_a($field,"ADD_Field_List") || is_a($field,"ADD_Field_Container_AutoComplete")) {
			return "enum";
		}
		if($field->getType()=="Number") {
			return "numeric";
		}
		if($field->getType()=="Expression") {
			return $field->getSetting("data_type","numeric");
		}
		return ADD_Util::convertToPerlish($field->getType());
	}
	
	function getEncounters($pid) {
		$explore = new Clinicle\Explore();
	}
}