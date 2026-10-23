<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Content_EncounterSummary_OptionTest extends ADD_Test_EncounterBase {
	protected $_conf = null;
		
		public function testDisplayOptionIfSectionNotExistingAndViewPermission() {
			$em = ADD::sys()->getEncounterManager();
                        $page = $this->_page;
			$cer = $this->getMock('ADD_EncounterRecord');
			$cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
			$er = $this->getMock('ADD_EncounterRecord');
			
			$er->expects($this->any())->method("exists")->will($this->returnValue(false));
			
                        $page->setPermission('view',true);
			
                        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('title'),$opts->toText());
   		 }
		
		public function testDisplayOptionIfSectionExistsAndViewPermission() {
                        $page = $this->_page;
			$em = ADD::sys()->getEncounterManager();
			
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			$em->setExpected($page->getSetting('encounter'),'child',$er);
                        $page->setPermission('view',true);
			
                        $opts = $page->createMenu();
			$this->assertEquals($page->getSetting('no-menu') ? null : $page->getSetting('title'),$opts->toText());
   		 }
		
		public function testNoDisplayOptionIfSectionExistsAndNoPermission() {
	        $page = $this->_page;
			
			list($per,$er,$cer) =  $this->setupEncounterRecord();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
	        
	        $opts = $page->createMenu();
			$this->assertEmpty($opts->toText());
   		 }
		
		public function testDisplayIncompleteIconIfLinkedEncountersNotCompletedButNotExist() {
	        $page = $this->_page;
			if($page->getExpectedPermissions()==array("view")) {
                 		$this->markTestSkipped();
						return;
					}
                 $this->givenLinkedFormsHaveProperties(false,"0");

                       $this->givenPageEncounterExists();
			
                        $page->setPermission('view',true);
			
                        $opts = $page->createMenu();
			$this->assertEquals("icon-remove",$page->createMenuIcon());
   		 }
                 
                 public function testDisplayIncompleteIconIfLinkedEncountersNotCompletedButExist() {
                    $page = $this->_page;
					if($page->getExpectedPermissions()==array("view")) {
                 		$this->markTestSkipped();
						return;
					}
                    $this->givenLinkedFormsHaveProperties(true,"0");
                    $this->givenPageEncounterExists();


                    $page->setPermission('view',true);

                    $opts = $page->createMenu();
                    $this->assertEquals("icon-remove",$page->createMenuIcon());
   		 }
                 
                 public function testDisplayCompleteIconIfLinkedEncountersCompletedAndExist() {
                    $page = $this->_page;
                    $this->givenLinkedFormsHaveProperties(true,"1");
                    $this->givenPageEncounterExists();

                    $page->setPermission('view',true);

                    $opts = $page->createMenu();
                    $this->assertEquals("icon-ok",$page->createMenuIcon());
   		 }
                 
                 private function givenPageEncounterExists() {
                     $em = ADD::sys()->getEncounterManager();
			
                     $er = $this->getERMock();
                    $er->expects($this->any())->method("exists")->will($this->returnValue(true));
                    $em->setExpected($this->_page,'child',$er);
                    return $er;
                 }
                 
		private function givenLinkedFormsHaveProperties($exists,$isComplete) {
                    $this->_page->setupComponents();
                        $form = $this->_page->getComponent('Form');
			$em = ADD::sys()->getEncounterManager();
			$cer = $this->getERMock();
                        $emap=array( array("is_complete",$isComplete) );
                        $cer->expects($this->any())->method("exists")->will($this->returnValue($exists));
                        $cer->expects($this->any())->method("getEncData")->will($this->returnValueMap($emap));
			foreach($form->getFields() as $field) {
                            if (!is_a($field,"ADD_Field_Button_PageLink")) continue;
                            $cpageName = $field->getSetting('section');
                            $cpage = $this->_module->getPage($cpageName);
                            $em->setExpected($cpage,"child",$cer);
                        }
                 }
                 
		
}
?>	