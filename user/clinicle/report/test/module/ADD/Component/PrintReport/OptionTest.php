<?php

class ADD_Component_PrintReport_OptionTest extends ADD_Test_EncounterBase {
	public function setUp() {
                parent::setUp();
        }
        
        public function testPrintReportOptionNotDisplayedIfNoPermission() {
            $page = $this->_page;
            $this->assertEquals(null,$page->createMenu()->output());
        }
        
        public function testPrintReportOptionDisplayedIfPermission() {
            $page = $this->_page;
            $page->setPermission('view',true);
            $this->givenMockPatientIsSelected();
            $per = $this->getERMock();
            $per->expects($this->any())->method("exists")->will($this->returnValue(true));
            $per->expects($this->once())->method("getId")->will($this->returnValue(99));
            ADD::sys()->getEncounterManager()->setExpected($page->getParent(),"child",$per);
            $opts = $page->createMenu();
            $rname = $page->getSetting('report-name');
            $uri = "/report?pid=123&name=$rname&id=99&module=".$page->getModule()->getName();
            $expOpt = '<li><a target="_blank" href="'.$uri.'"><i class="icon-print"></i><span></span>'.$page->getSetting('title').'</a></li>';
           
            $this->assertEquals($expOpt,$opts->output());
        }
		
}
?>	