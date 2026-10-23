<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Content_EncounterEntry_OptionTest extends ADD_Test_EncounterBase {
	protected $_conf = null;
	protected $expectedERFunction="child";
	protected $sectionExistPermission="create";	
		public function testDisplayOptionIfSectionNotExistingAndCreatePermission() {
			$em = ADD::sys()->getEncounterManager();
                        $page = $this->_page;
						
	        $page->setPermission('create',true);
			
	        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title'),$opts->toText());
   		 }
		
		public function testDisplayOptionIfSectionExistsAndCreateEditPermission() {
            $page = $this->_page;
			$this->givenPageEncounterExistsAndHasValues(array(),$page,$this->expectedERFunction);
			$page->setPermission($this->sectionExistPermission,true);
			
            $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title'),$opts->toText());
   		}
		
		public function testNoDisplayOptionIfSectionExistsAndNoPermission() {
	        $page = $this->_page;
			$this->givenPageEncounterExistsAndHasValues(array(), $page);

	        $opts = $page->createMenu();
			$this->assertEmpty($opts->toText());
   		 }
		
		public function testDisplayOptionIfSectionExistsAndViewPermission() {
	        $page = $this->_page;
			$em = ADD::sys()->getEncounterManager();
			
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			$em->setExpected($page->getSetting('encounter'),$this->expectedERFunction,$er);
			
	        $page->setPermission('view',true);
			
	        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title')." (view only)",$opts->toText());
   		 }
		
		
}
?>	