<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_RTF {
	private $_fileName;
	private $_phpWord;
	private $_textLines=array();
	private $_populatedLines=array();
	function __construct($filename) {
		$this->_fileName=$filename;
		$data = file($filename);
		$phpWord = new \PhpOffice\PhpWord\PhpWord();
		$this->_phpWord = $phpWord->loadTemplate($this->_fileName);
	}
	function cleanOutput() {
		return file_get_contents($this->_fileName);
	}
	/*
	function fillFieldsUsingData(ADD_Report_DataPopulate $dataPopulate) {
		$lineCount = 0;
		$withValues = array();
		
		$lines = $this->_textLines;
		
		list($fieldList,$fieldElements) = $this->_getFieldsFromDocument();
		$letterData = $dataPopulate->populateDataUsingNames($fieldList);
		$populatedLines = array();
		foreach($fieldElements as $fieldElement) {
			list($textLine, $matchedFields)=$fieldElement;
			
			foreach($matchedFields as $matchedField) {
				$textLine = str_replace("[".$matchedField."]",ADD_Util::val($matchedField,$letterData),$textLine);
			}
			$populatedLines[]=$textLine;
		}
		$this->_populatedLines = $populatedLines;
	}
	
	function _getFieldsFromDocument() {
		$textLines = $this->_textLines;
		$fieldList = array();
		$fieldElements = array();
		foreach($textLines as $textLine) {
			$matches=array();
			ADD::log()->info("textLine:$textLine");
			if (preg_match_all("/\[(.*?)]/",$textLine,$matches)) {
				$fieldList=array_merge($fieldList,$matches[1]);
				$fieldElements[]=array($textLine,$matches[1]);
			}
			else {
				$fieldElements[]=array($textLine,array());
			}
		}
		return array($fieldList,$fieldElements);
	}
	*/
	function fillFieldsUsingData(ADD_Report_DataPopulate $dataPopulate) {
		$lineCount = 0;
		$withValues = array();
		
		//$sections = $this->_phpWord->getSections();
		$fieldList = $this->_getFieldsFromDocument();
		$letterData = $dataPopulate->populateDataUsingNames($fieldList);
		
		foreach($fieldList as $fieldItem) {
			$this->_phpWord->setValue($fieldItem,ADD_Util::val($fieldItem,$letterData));
		}
		/*
		foreach($fieldElements as $fieldElement) {
			list($element, $matchedFields)=$fieldElement;
			$textLine = $element->getText();
			
			foreach($matchedFields as $matchedField) {
				$this->_phpWord->setValue($matchedField,ADD_Util::val($matchedField,$letterData),$textLine);
		
				//$textLine = str_replace("{".$matchedField."}",ADD_Util::val($matchedField,$letterData),$textLine);
			}
			//$element->setText($textLine);
		}
		*/
	}
	function _getTextElements($element, &$textElements) {
		if(method_exists($element,"getText")) $textElements[]=$element;
		if(!method_exists($element,"getElements")) return;
		
		foreach($element->getElements() as $childElement) {
			$this->_getTextElements($childElement,$textElements);
		}
	}
	
	function _getFieldsFromDocument() {
		$vars = array_map(function($var) { return strip_tags($var); }, $this->_phpWord->getVariables());
		return $vars;
		
		$sections = $this->_phpWord->getSections();
		$fieldList = array();
		$fieldElements = array();
		
		$textElements = array();
		foreach($sections as $section) {
			$this->_getTextElements($section,$textElements);
		}
			
		foreach($textElements as $element) {
			$textLine = $element->getText();
			
			if (preg_match_all("/\{(.*?)\}/",$textLine,$matches)) {
				$fieldList=array_merge($fieldList,$matches[1]);
				$fieldElements[]=array($element,$matches[1]);
			}

		}
		return array($fieldList,$fieldElements);
	}
	
	function saveFile($fileName) {
		$this->_phpWord->saveAs($fileName);
	}
	 
	/*
	
	function saveFile($fileName) {
		file_put_contents($fileName,implode("\n",$this->_populatedLines));
	}
	 * 
	 */
}
