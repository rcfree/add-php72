<?php




class ADD_Page_ReportOutput extends ADD_Page {
    private $_report;
	
	function isWebContent() {
		return false;
	}
	function getReport() {
		return $this->_report;
	}
	function createMenuPath() {
		return "report";
	}
	
	function createOutput($values = array()) {
		
		$this->_report->init($this->_reportConfig);
	 
		$this->_report->populate($this);
		$this->_report->generate($this);
		
		
		$content = $this->_report->getContent();
		$content->clearBuffer();
		
		list($reportFilename,$ts)=$this->_report->createReportFilenameAndTs();
		$this->_report->setReportFilename($reportFilename);
		
        $reportName = $this->_report->getReportConfig()->getReportName();
        $content->toFile($reportFilename);

		$adb = ADD::db('audit');
        
        $adb->auditReport($reportName, $reportFilename,$ts, ADD::sys()->getParam('id'));
		return $content;
	}
	
	function setupComponents() {
		$add = ADD::sys();
		$this->_modName = $add->assertParam('module');
		$module = $add->getModule($this->_modName);
		$name = $add->assertParam('name');
		$empty = $add->getParam('empty');
		$id = $add->getParam('id');
		$this->_reportConfig = new ADD_Report_Config($module,$name,$empty);
		$rsect = $this->_reportConfig->getReportSection();
		
		$this->_report = ADD_Util::loadClass("ADD_Report_".$rsect->getSetting('report_class','Standard'));
		if(!$empty && !$id) {
			$rpage = $module->getPage($rsect->getSetting('section'));
			$er = $rpage->getEncounterRecord();
			$er->retrieve();
			ADD::sys()->setParam('id',$er->getId());
		}
		
		
		$this->_report->initContent();
	}
	
	function isAvailable() {
		$rc = $this->_reportConfig;
		
		$rcModule = $rc->getModule();
		$rcName = $rc->getReportName();
		$rcPage = $rcModule->getPage($rcName);
		if(!$rcPage) return true;
		if (!$rcPage->getPermission('print')) {
			return false;
		}
		return true;
	}
	
}
