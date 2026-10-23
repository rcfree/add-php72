<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class ADD_Report_Letter extends ADD_Report_Base {
	function getContentClass() {
		return "Rtf";
	}	
	function initSpecific() {
		$rsect = $this->getReportConfig()->getReportSection();
		$letterPath = $this->getModule()->getPath()."/letter/".$rsect->getSetting('file');	
		$this->_rtf = new ADD_Tool_RTF($letterPath);
		
	}
	
	public function populate($pageLike) {
		$this->_dataPopulate = new ADD_Report_DataPopulate(ADD::sys()->getPatientId(), $this->_reportConfig->getModule()->getName(), ADD::sys()->getParam('id'), $pageLike,null,true); 
		
		$this->_rtf->fillFieldsUsingData($this->_dataPopulate);
		$this->setData($this->_dataPopulate->getData());
	}
	
	function generate() {
		$this->getContent()->setRtf($this->_rtf);
	}
	
	function getReportExtension() {
		return "docx";
	}
}