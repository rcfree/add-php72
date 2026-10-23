<?php
$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Component_SummaryUpdate_AfterSubmitTest extends ADD_Test_EncounterBase {
	protected $_cer = null;
        protected $_uer = null;
        static $_expSubmit;
	function setUp() {
		parent::setUp();
		$this->loadTestConfig();
		$em = ADD::sys()->getEncounterManager();
		$cer = $this->getMock('ADD_EncounterRecord');
		$cer->expects($this->any())->method("getObsValue")->will($this->returnCallback(function($name) {
			return ADD_Component_SummaryUpdate_AfterSubmitTest::$_expSubmit[$name];
		}));
                
                $page = $this->_page;
		$em->setExpected($page->getSetting('encounter'),"child",$cer);
		$em->setExpected($page->getSetting('encounter'),"record",$cer);
                
                $uer = $this->getERMock();
                $upage = $this->_module->getPage($page->getSetting('summary-section'));
                $em->setExpected($upage->getSetting('encounter'),"child",$uer);
                $em->setExpected($upage->getSetting('encounter'),"child",$uer);
                
		self::$_expSubmit = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
		
		$this->_cer = $cer;
                $this->_uer = $uer;
	}
	function testCreateAfterSubmitWithNoValuesIsOnGoing() {
		$this->givenMockPatientIsSelected();
		$form = $this->_page->getComponent('Form');
		
		$expValues = $this->populateExpectedSummaryObs($form,"On-going");

		foreach(ADD::sys()->getModules() as $module) {
			foreach($module->getPages() as $page) {
				$page->afterSubmit($this->_page,$page);
			}
		}
		$this->assertHasMessage("Created summary record and added observations");
	}
	
	function testCreateAfterSubmitWithIsCompleteSetAsComplete() {
		$this->givenMockPatientIsSelected();
		$form = $this->_page->getComponent('Form');
		$page = $this->_page;
		
		$expValues = $this->populateExpectedSummaryObs($form,"Complete");
		
		$uer = $this->_uer;
		$uer->expects($this->any())->method("exists")->will($this->returnValue(false));
		$uer->expects($this->once())->method("store")->with($this->equalTo($expValues));
		$this->_cer->expects($this->once())->method("getEncData")->with($this->equalTo("is_complete"))->will($this->returnValue(true));
		
		foreach(ADD::sys()->getModules() as $module) {
			foreach($module->getPages() as $page) {
				$page->afterSubmit($this->_page,$page);
			}
		}
		$this->assertHasMessage("Created summary record and added observations");
	}
	
	
	function testCreateAfterSubmitWithExampleValues() {
		$this->givenMockPatientIsSelected();
		$form = $this->_page->getComponent('Form');
				
		$expValues = $this->populateExpectedSummaryObs($form,"On-going");

		$uer = $this->_uer;
		$uer->expects($this->any())->method("exists")->will($this->returnValue(false));
		$uer->expects($this->once())->method("store")->with($this->equalTo($expValues));
		$this->_cer->expects($this->once())->method("getEncData")->with($this->equalTo("is_complete"))->will($this->returnValue(false));
		
		foreach(ADD::sys()->getModules() as $module) {
			foreach($module->getPages() as $page) {
				$page->afterSubmit($this->_page,$page);
			}
		}
		$this->assertHasMessage("Created summary record and added observations");
	}
	function populateExpectedSummaryObs($form,$expectedStatus) {
		$expValues = array();
		$page = $this->_page;
		foreach($form->getFields() as $field) {
			$summaryObs = $field->getSetting('summary_obs');
			if (!$summaryObs) continue;
			$summaryFieldName = $page->getSetting('SummaryUpdate.' . $summaryObs,$summaryObs);
            if ($field->getSetting('summary_type') == 'status') {
                $expValues[$summaryFieldName] = $expectedStatus;
            } 
            else {
          		$expValues[$summaryFieldName] = self::$_expSubmit[$field->getName()];
            }
		}
		return $expValues;
	}
	function testUpdateAfterSubmitWithExampleValues() {
		$uer = $this->_uer;
		
		$this->givenMockPatientIsSelected();
		$form = $this->_page->getComponent('Form');
		
		$expValues = $this->populateExpectedSummaryObs($form,"On-going");

		$uer->expects($this->any())->method("exists")->will($this->returnValue(true));
		$uer->expects($this->once())->method("store")->with($this->equalTo($expValues));
		$this->_cer->expects($this->once())->method("getEncData")->with($this->equalTo("is_complete"))->will($this->returnValue(false));
		
		foreach(ADD::sys()->getModules() as $module) {
			foreach($module->getPages() as $page) {
				$page->afterSubmit($this->_page,$page);
			}
		}
		$this->assertHasMessage("Updated summary record with new observations");
	}
}
