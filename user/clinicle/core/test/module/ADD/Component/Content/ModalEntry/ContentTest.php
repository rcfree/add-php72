<?php


class ADD_Component_Content_ModalEntry_ContentTest extends ADD_Test_EncounterBase {
	protected $_conf = null;
		public function setUp() {
			parent::setUp();
			
			$this->loadTestConfig();
			ADD::db('dictionary')->expects($this->any())->method("hasDictionary")->will($this->returnValue(true));
			ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3"); }));
	    	    
		
		}
		public function testFormTitleIsCorrect() {
			$this->assertEquals($this->_page->getSetting('title'),$this->_page->createTitle());
		}
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
		
		public function testFormDisplayedIfCreatePermission() {
                    $em = ADD::sys()->getEncounterManager();
                        $page = $this->_page;
                        $ppage = $page->getParent();
			$this->givenMockPatientIsSelected();
                        $er = $this->getERMock();
                        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
			$cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
			$em = ADD::sys()->getEncounterManager();
                        $em->setExpected($page,"child",$cer);
                        $em->setExpected($ppage,"child",$er);
			$patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
                        ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
			
                        $page->setPermission('edit',true);
			$page->setupComponents();
                        $page->createContent();
			$this->assertGreaterThan(0,strlen(ADD::web()->getContent()));
			$this->assertHasNoWarnings();
   		 }
                 
               
		
		public function testFormSubmitIfCreatePermission() {
	        $page = $this->_page;
			$this->givenTokenDoesNotExist();
			$this->givenMockPatientIsSelected();
			$cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
			$cer->expects($this->exactly(1))->method("store");
			ADD::sys()->getEncounterManager()->setExpected($page,"newChild",$cer);
			$patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
                        ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
			
                        $page->setPermission('create',true);
			$page->setupComponents();
                        $page->submitForm(array('__action'=>'create','__id'=>''));
			$this->assertHasNoWarnings();
   		}
		
		/**
		 * @expectedException ADD_AccessException
		 * @expectedExceptionMessage Sorry you do not have permission to edit
		 */
		public function testFormSubmitFailsIfRecordAndOnlyCreatePermission() {
                        $page = $this->_page;
                        $em = ADD::sys()->getEncounterManager();
			ADD::sys()->setParam('t','TOKEN');
                        $this->givenMockPatientIsSelected();
			ADD::db('patient')->expects($this->never())->method("doesPatientExist")->will($this->returnValue(true));
			
			$cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
			$cer->expects($this->never())->method("store");
                        
			$em->setExpected($page,"child",$cer);
			
			$page->setPermission('create',true);
			
			$page->setupComponents();
			$page->submitForm(array('__action'=>"edit",'__id'=>''));
   		}
		
   		/**
		 * @expectedException ADD_AccessException
		 * @expectedExceptionMessage Sorry you do not have permission to create
		 */
		public function testFormSubmitFailsIfNoRecordAndNoCreatePermission() {
	        $page = $this->_page;
			ADD::sys()->setParam('t','TOKEN');
			$this->givenMockPatientIsSelected();
			list($per,$er,$cer) =  $this->setupEncounterRecord();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
			$cer->expects($this->never())->method("store");
                        ADD::sys()->getEncounterManager()->setExpected($page,"child",$cer);
			
			$page->setupComponents();
			$page->submitForm(array('__action'=>"create",'__id'=>''));
			$this->assertHasNoWarnings();
   		}
		
		static $dataSubmitted = false;
		static $expConf = null;
		
		public function testFormSubmitCreateWithExampleData() {
                        $conf = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
			$page = $this->_page;
			ADD::sys()->setParam('t','TOKEN');
			$this->givenMockPatientIsSelected();
			$this->givenTokenDoesNotExist();
			$cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
			
			ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
                        $page->setPermission('create',true);
			$page->setupComponents();
			$expCount = $page->hasComponent('Locking') ? $this->exactly(2) : $this->once();
	        $this->_submitData = $conf;
			$this->_submitData['__action']='create';
			$this->_submitData['__id']='';
	        self::$expConf = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
	        $withMap = array(array($conf),array(null));
			
                        $cer->expects($expCount)->method("store")->with($this->callback(function($args) {
	        	 if (ADD_Util::isEmpty($args) && ADD_Component_Content_ModalEntry_ContentTest::$dataSubmitted) return true;
				  
	        	 if(ADD_Component_Content_ModalEntry_ContentTest::$expConf==$args) {
	        	 	ADD_Component_Content_ModalEntry_ContentTest::$dataSubmitted=true;
	        	 	return true;
				 }
				 ADD::log()->info("Arrays do not match:\nExpected:".print_r(ADD_Component_Content_ModalEntry_ContentTest::$expConf,1)."\nObserved:".print_r($args,1));	        	
			}));
			ADD::sys()->getEncounterManager()->setExpected($page,"newChild",$cer);
                        $page->submitForm($this->_submitData);
			$this->assertHasNoWarnings();
   		}
   		
		public function testFormSubmitUpdateWithExampleData() {
                     $conf = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
		
			$page = $this->_page;
                        $ppage = $page->getParent();
			ADD::sys()->setParam('t','TOKEN');
			$this->givenMockPatientIsSelected();
			$this->givenTokenDoesNotExist();
			$er = $this->getERMock();
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			
                        $cer = $this->getERMock();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
			
			ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
                        $page->setPermission('edit',true);
			$page->setupComponents();
			$expCount = $page->hasComponent('Locking') ? $this->exactly(2) : $this->once();
                        if(count($this->_conf->getSectionSettings('FormSubmitWithExampleData'))==0) throw new Exception("No fields provided in FormSubmitWithExampleData in config file");
        
                        $this->_submitData = $conf;
			$this->_submitData['__action']='edit';
			$this->_submitData['__id']='4';
	        
                        $withMap = array(array($conf),array(null));

                        $cer->expects($expCount)->method("store")->with($this->callback(function($args) {
	        	 if (ADD_Util::isEmpty($args) && ADD_Component_Content_ModalEntry_ContentTest::$dataSubmitted) return true;
				  
	        	 if(ADD_Component_Content_ModalEntry_ContentTest::$expConf==$args) {
	        	 	ADD_Component_Content_ModalEntry_ContentTest::$dataSubmitted=true;
	        	 	return true;
				 }
				 $diff = array_diff_key(ADD_Component_Content_ModalEntry_ContentTest::$expConf, $args);
				 ADD::log()->info("Arrays do not match:\nExpected:".print_r(ADD_Component_Content_ModalEntry_ContentTest::$expConf,1)."\nObserved:".print_r($args,1)."\nDifferent:".print_r($diff,1));	        	
			}));
                        ADD::sys()->getEncounterManager()->setExpected($ppage,"child",$er);
                        ADD::sys()->getEncounterManager()->setExpected($page,"child",$cer);
                        $page->submitForm($this->_submitData);
			$this->assertHasNoWarnings();
   		}
		/**
		 * @expectedException ADD_AccessException
		 * @expectedExceptionMessage Sorry you do not have permission to edit
		 */
		public function testFormUpdateWithExampleDataFailsWhenOnlyViewPerms() {
                    $conf = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
		if(count($this->_conf->getSectionSettings('FormSubmitWithExampleData'))==0) throw new Exception("No fields provided in FormSubmitWithExampleData in config file");
        
			$page = $this->_page;
			ADD::sys()->setParam('t','TOKEN');
			$this->givenMockPatientIsSelected();
			//$this->givenTokenDoesNotExist();
			list($per,$er,$cer) =  $this->setupEncounterRecord();
			$cer->expects($this->any())->method("exists")->will($this->returnValue(true));
			
			$page->setPermission('view',true);
			$page->setupComponents();
			$expCount = $page->hasComponent('Locking') ? $this->exactly(2) : $this->once();
                        $this->_submitData = $conf;
			$this->_submitData['__action']='edit';
			$this->_submitData['__id']='';
	        
	        $withMap = array(array($conf),array(null));
			
	        $cer->expects($this->never())->method("store");
			
	        $page->submitForm($this->_submitData);
			$this->assertHasNoWarnings();
   		}
}

?>