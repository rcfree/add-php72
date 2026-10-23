<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

abstract class ADD_Report_Base {
	protected $_reportConfig;
	protected $_data;
	protected $_content;

	function getModule() {
		return $this->_reportConfig->getModule();
	}
	function getContentClass() {
		throw new ADD_NullValueProvidedException("No 'getContentClass' function implemented");
	}

	function initContent() {
		$this->_content = ADD::web()->newContent($this->getContentClass());
	}
	function getContent() {
		return $this->_content;
	}
	function init(ADD_Report_Config $reportConfig) {
		$add = ADD::sys();	
		$add->setState('report',true);
		$edb = ADD::db('encounter');
		$encType = $reportConfig->getReportSection()->assertSetting('encounter');
		$reportType = $add->getParam('type');
		//if(!$reportType) {
			$encForm = $edb->getEncounterFormWithType($encType);
			if(!$encForm) {
				$encForm = $edb->getSectionTypeWithName($encType);
			}
			$reportType = "encounter/section";
//		}
//		else {
//			$encForm = $reportType == "section" ? $edb->getSectionTypeWithName($encType) : $edb->getEncounterFormWithType($encType);
//		}
		if (!$encForm) throw new ADD_ReportException("$reportType type '".$encType."' not defined in database");
		
		$this->setEncounterType($encForm);
		$this->setReportConfig($reportConfig);
		
		$this->initSpecific();
		$this->_content->init($this);
	}
		
	public function setEncounterType($encounterType) {
		$this->_encounterType = $encounterType;
	}
	public function getEncounterType() {
		return $this->_encounterType;
	}
	
	public function setReportConfig($reportConfig) {
		$this->_reportConfig = $reportConfig;
	}

	public function getReportConfig() {
		return $this->_reportConfig;
	}
	
	public function isEmpty() {
		return $this->_reportConfig->isEmpty();
	}
	
	public function createReportFilenameAndTs() {
        $add = ADD::sys();

        $ts = time();

        $reportPath = $add->assertConfig('report_path');

        //$cwd = getcwd();
        //chdir($reportPath);
        $dirTree = date("Y/m/d",$ts);
        
		//chdir($cwd);
        $reportPath = $reportPath."/".$dirTree;

        $user = ADD::sys()->getAccessManager()->getUser();

        $reportName = $this->_reportConfig->getReportName();

        $data = $this->getData();

        $reportFilename = $this->_reportConfig->isEmpty() ? 'blank_'.$add->getParam('module')."_".$reportName : $add->getParam('module')."_".$reportName."_".$data['patient.identifier']."_".ADD_Field_Util::timestamp($ts);
        $reportFilename.="_".$user->username;
        $reportFilename=$reportPath."/".$reportFilename.".".$this->getReportExtension();
		
		$reportPathInfo = pathinfo($reportFilename);
		$reportDir = $reportPathInfo['dirname'];
		if (!file_exists($reportDir)) {
        	mkdir($reportDir,0777,true);
			chmod($reportDir,0777);
			if(!file_exists($reportDir)) throw new ADD_ReportException("Unable to create directory '$reportDir'");
			
        }
		
        return array($reportFilename,$ts);
    }
	
	/**
     * @param null $reportFilename
     */
    public function setReportFilename($reportFilename)
    {
        $this->_reportFilename = $reportFilename;
    }

    /**
     * @return null
     */
    public function getReportFilename()
    {
        return $this->_reportFilename;
    }
	
	function setData($data) {
		$this->_data = $data;
	}
	
	function getData() {
		return $this->_data;
	}
	
	function unsetData($name) {
		if(isset($this->_data[$name])) unset($this->_data[$name]);
	}
	function getReportExtension() { }
}