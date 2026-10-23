<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Report_Config {
	private $_empty;
	private $_module;
	private $_reportSection=null;
	
	function __construct(ADD_Module $module,$name,$empty) {
		$add = ADD::sys();	
		$add->setState('report',true);
		$edb = ADD::db('encounter');
		
		$reportPage = $module->getPageInStructure($name);
		if ($reportPage) {
			$rsect = new ADD_Config($reportPage->getSettings());
		}
		else {
			$modPath = $module->getPath();
			
			$template = new ADD_Config();
			$template->loadFile($modPath."/report.ini");

			$rsect = $template->getSection($name);
		}
		$this->_module = $module;
		$this->_empty = $empty;

		if (empty($rsect)) throw new ADD_PageException("Report section for '".$name."' was not defined in page.ini or report.ini");
		$this->setReportName($name);
		$this->setReportSection($rsect);
		
	}
	
	public function getModule() {
		return $this->_module;
	}
	
	public function setReportSection($reportSection) {
		$this->_reportSection = $reportSection;
	}
	public function getReportSection() {
		return $this->_reportSection;
	}
	
	public function setReportName($reportName) {
		$this->_reportName = $reportName;
	}
	public function getReportName() {
		return $this->_reportName;
	}
	
	public function isEmpty() {
		return $this->_empty;
	}
	
	public function setEmpty($empty) {
		$this->_empty = $empty;
	}
	
	
}