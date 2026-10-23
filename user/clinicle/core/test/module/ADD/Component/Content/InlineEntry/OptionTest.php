<?php



class ADD_Component_Content_InlineEntry_OptionTest extends ADD_Test_EncounterBase {
		
		public function testDisplayOptionIfSectionNotExistingAndCreatePermission() {
	        $page = $this->_page;
			
			//list($per,$er,$cer) = $this->setupEncounterRecord();
			//$er->expects($this->any())->method("exists")->will($this->returnValue(false));
			
	        $page->setPermission('create',true);
			
	        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title')." [0]",$opts->toText());
   		 }
		
		public function testDisplayOptionIfSectionExistsAndCreatePermission() {
	        $page = $this->_page;
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			$page->setPermission('create',true);
			
	        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title')." [0]",$opts->toText());
   		 }
		
		public function testDisplayOptionWithMultipleEntriesAndCreatePermission() {
	        $em = ADD::sys()->getEncounterManager();	
	        $page = $this->_page;
			$ppage = $page->getParent();
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
                        $cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
			
                        $em->setExpected($page,"children",array($cer,$cer));
			$em->setExpected($ppage,"child",$er);
			
			$page->setPermission('edit',true);
			
	        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title')." [2]",$opts->toText());
   		 }
		
		public function testNoDisplayOptionIfSectionExistsAndNoPermission() {
                        $em = ADD::sys()->getEncounterManager();
                        $page = $this->_page;
                        $ppage = $page->getParent();
                        $er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
                        $cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
                        
                        $em->setExpected($ppage,"record",$er);
			$em->setExpected($page,"children",array($cer,$cer));
			$em->setExpected($page,"child",$cer);
			
                        $opts = $page->createMenu();
			$this->assertEmpty($opts->toText());
   		}
		
		public function testDisplayOptionIfSectionExistsAndViewPermission() {
                        $em = ADD::sys()->getEncounterManager();
                     $page = $this->_page;
                     $ppage = $page->getParent();
                        $er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
                        $cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
                        
                        $em->setExpected($ppage,"record",$er);
			$em->setExpected($page,"children",array($cer,$cer));
			$em->setExpected($page,"child",$cer);
			$page->setPermission('view',true);
                        
                        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title')." [0] (view only)",$opts->toText());
   		 }
		
}
?>	