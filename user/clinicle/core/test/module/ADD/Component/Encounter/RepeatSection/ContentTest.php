<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_RepeatSection_ContentTest extends ADD_Test_EncounterBase {
	
	public function testCreateNewSectionIfPermission() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		
		ADD::sys()->getAccessManager()->setCurrentPage($page);
		$this->givenMockPatientIsSelected();
		$this->givenTokenDoesNotExist();
        
		$per = $this->getMock('ADD_EncounterRecord');
		$per->expects($this->any())->method("exists")->will($this->returnValue(true));
		$per->expects($this->once())->method("retrieve");
		$em->setExpected($ppage->getSetting('encounter'),"record",$per);
		
		foreach($this->_module->getPages() as $mpage) {
			$mpage->setPermission('create',true);
        }
		
		$cer = $this->getMock('ADD_EncounterRecord');
                $cer->expects($this->once())->method("store");
		$em->setExpected($page->getSetting('encounter'),"newChild",$cer);
		$em->setExpected($page->getSetting('encounter'),"record",$cer);
		
        $opts = $page->createContent();
		$this->assertHasNoWarnings();
        $this->assertHasContent("");
	}	
	/**
	 * @expectedException ADD_EncounterNotFoundException
	 * @expectedExceptionMessage with ID '123' does not exist
	 */
	public function testCannotDeleteSectionIfPermissionButItDoesNotExist() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		ADD::sys()->getAccessManager()->setCurrentPage($page);
		$this->givenMockPatientIsSelected();
		$this->givenTokenDoesNotExist();
        ADD::sys()->setParam('action','delete');
		ADD::sys()->setParam('eid',123);
		$per = $this->getMock('ADD_EncounterRecord');
		$per->expects($this->any())->method("exists")->will($this->returnValue(true));
		$per->expects($this->once())->method("retrieve");
		$em->setExpected($ppage->getSetting('encounter'),"record",$per);
		
		foreach($this->_module->getPages() as $mpage) {
            $mpage->setPermission('delete',true);
        }
		
		$opts = $page->createContent();
		$this->assertHasNoWarnings();
        $this->assertIncludesMessage($page->getSetting('title')." record was deleted successfully");
		$this->assertHasContent("");
	}	
	
	public function testDeleteNewSectionIfPermission() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		ADD::sys()->getAccessManager()->setCurrentPage($page);
		ADD::sys()->setParam('eid',123);
		$this->givenMockPatientIsSelected();
		$this->givenTokenDoesNotExist();
        ADD::sys()->setParam('action','delete');
		
		$per = $this->getMock('ADD_EncounterRecord');
		$per->expects($this->any())->method("exists")->will($this->returnValue(true));
		$per->expects($this->once())->method("retrieve");
		$cer = $this->getMock('ADD_EncounterRecord');
		$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
		$cer->expects($this->once())->method("delete");
        
		$em->setExpected($ppage->getSetting('encounter'),"record",$per);
		$em->setExpected(123,"recordById",$cer);
		
		foreach($this->_module->getPages() as $mpage) {
            $mpage->setPermission('delete',true);
        }
		
		$opts = $page->createContent();
		$this->assertHasNoWarnings();
        $this->assertIncludesMessage($page->getSetting('title')." record was deleted successfully");
		$this->assertHasContent("");
	}	
	
	/**
	 * @expectedException ADD_NoPermissionToAccessException
	 * @expectedExceptionMessage You do not have permission to 'create'
	 */
	public function testCannotCreateNewSectionIfNoPermission() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		ADD::sys()->getAccessManager()->setCurrentPage($page);
		ADD::sys()->setParam('action','create');
		$this->givenMockPatientIsSelected();
		         
		
        $opts = $page->createContent();
		
	}	
	
	/**
	 * @expectedException ADD_NoPermissionToAccessException
	 * @expectedExceptionMessage You do not have permission to 'delete'
	 */
	public function testCannotDeleteSectionIfNoPermission() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		ADD::sys()->getAccessManager()->setCurrentPage($page);
		ADD::sys()->setParam('action','delete');
		$this->givenMockPatientIsSelected();
		         
		
        $opts = $page->createContent();
		
	}	
}
?>
