<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_Section_OptionTest extends ADD_Test_EncounterBase {
		
		public function testDisplayAddOptionIfSectionNotExistingAndCreatePermission() {
	        $em = ADD::sys()->getEncounterManager();	
	        $page = $this->_page;
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(false));

			foreach($this->_module->getPages() as $mpage) {
				ADD::log()->warn("page:".$mpage->getName());
            
	            $mpage->setPermission('create',true);
	        }
			$em->setExpected($page,"child",$er);
	        $opts = $page->createMenu();
			$this->assertEquals("&nbsp;Add patient to ".$page->getSetting('label')." section..",$opts->toText());
   		 }
		/*
		public function testDisplayOptionsIfSectionExistsAndUserHasCreatePermission() {
	        $em = ADD::sys()->getEncounterManager();	
	        	
	        $page = $this->_page;
                $er = $this->getERMock();
                $er->expects($this->any())->method("exists")->will($this->returnValue(true));

                foreach($this->_module->getPages() as $mpage) {
	            $mpage->setPermission('create',true);
	        }
		$encType = $page->getSetting('encounter');
	        $em->setExpected($encType,"record",$er);
		$em->setExpected($encType,"child",$er);
			
	        $opts = $page->createMenu();
			$expOpts=$page->getSetting('title');
			
			foreach($page->getChildPages() as $cpage) {
				$expOpts.=$cpage->createMenu()->toText();
			}
			
			$this->assertEquals($expOpts,$opts->toText());
   		 }
		
		public function testDisplayViewableIfSectionExistingAndViewPermission() {
			$em = ADD::sys()->getEncounterManager();	
	        
                        $page = $this->_page;
			$cpages = $page->getChildPages();
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			$em->setExpected($page->getSetting('encounter'),"child",$er);
                        foreach($cpages as $cpage) {
                            $cencType = $cpage->getSetting('encounter');
                        
                            $em->setExpected($cencType,"record",$er);
                            $em->setExpected($cencType,"child",$er);
                        }
			foreach($this->_module->getPages() as $mpage) {
	            $mpage->setPermission('view',true);
	        }
			
	        $opts = $page->createMenu();
			
			$expOpts=$page->getSetting('title');
			foreach($page->getChildPages() as $cpage) {
				$expOpts.=$cpage->createMenu()->toText();
			}
			$this->assertEquals($expOpts,$opts->toText());
   		 }
		/*
		public function testDisplayAddableLinkMatchesPageURI() {
	        $page = $this->_page;
			list($per,$er,$cer) = $this->setupEncounterRecord();
			$this->givenMockPatientIsSelected();
			$per->expects($this->any())->method("exists")->will($this->returnValue(false));
			
			foreach($this->_module->getPages() as $mpage) {
	            $mpage->setPermission('create',true);
	        }
			
	        $opts = $page->createMenu();
			ADD::log()->info("pos:".$opts->toHtml()." != ".$page->createMenuPath());
			$pos = strpos($opts->toHtml(),$page->createMenuPath());
			$this->assertGreaterThan(0,$pos);
   		}
		 * 
		 */
}
?>
