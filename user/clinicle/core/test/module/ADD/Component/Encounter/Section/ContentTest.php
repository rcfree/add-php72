<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_Section_ContentTest extends ADD_Test_EncounterBase {
		
	public function testCreateSectionIfNotExist() {
        $page = $this->_page;
        $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		$this->givenMockPatientIsSelected();
		$this->givenTokenDoesNotExist();
		$per = $this->getERMock();
		$per->expects($this->any())->method("exists")->will($this->returnValue(true));
		$per->expects($this->once())->method("retrieve");
		$em->setExpected($ppage->getSetting('encounter'),"record",$per);
		
		foreach($this->_module->getPages() as $mpage) {
			$mpage->setPermission('create',true);
        }
		
		$cer = $this->getMock('ADD_EncounterRecord');		
        $cer->expects($this->exactly(2))->method("exists")->will($this->returnValue(false));
		$cer->expects($this->exactly(2))->method("store");
		$em->setExpected($page->getSetting('encounter'),"child",$cer);
		
		
        $opts = $page->createContent();
		$this->assertHasNoWarnings();
		$this->assertHasMessage("Patient was added to the ".$page->getSetting('title')." section successfully");
		$this->assertHasContent("");
	}	
	
	/**
	 * @expectedException ADD_EncounterException
	 * @expectedExceptionMessage Patient already added 
	 */
	public function testCreateSectionFailsIfExists() {
            $page = $this->_page;
            $ppage = $page->getParentPage();
		$em = ADD::sys()->getEncounterManager();
		
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
        $cer->expects($this->exactly(2))->method("exists")->will($this->returnValue(true));
		$cer->expects($this->never())->method("store");
		$em->setExpected($page->getSetting('encounter'),"child",$cer);
		
		$opts = $page->createContent();
	}	
}
?>
