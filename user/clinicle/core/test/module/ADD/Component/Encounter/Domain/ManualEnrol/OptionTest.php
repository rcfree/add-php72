<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_Domain_ManualEnrol_OptionTest extends ADD_Test_EncounterBase {
    protected $addToDomain="";
	protected $addedToDomain="";
	public function setUp() {
		parent::setUp();
		$page = $this->_page;
		$createLabel = $page->getSetting('create-label');
        
		$this->addToDomain=$page->getSetting('title')."&nbsp;".($createLabel ? $createLabel : "Add patient to ".$page->getSetting('label'));
		$this->addedToDomain=$page->getSetting('title')."*";
	}
    public function testIfNoCreateAccessAndNoOtherAccessNoOptionsAreGenerated() {
        $page = $this->_page;
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
     public function testIfCreateAccessButNoPatientNoOptionsGenerated() {
        $page = $this->_page;
        $page->setPermission('create',true);  
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testIfNoAccessAndNoPatientNoOptionsGenerated() {
        $page = $this->_page;
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testIfCreateAccessButPatientDoesNotExistNoOptionsAreCreated() {
        $page = $this->_page;
        $page->setPermission('create',true);
        $this->givenMockPatientIsSelected();
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        ADD::db('patient')->expects($this->exactly(1))->method("getPatient")->will($this->returnValue(null));
        
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testIfCreateAccessAndPatientIsDeletedNoOptionsAreCreated() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        
        $page->setPermission('create',true);
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->exactly(1))->method("getPatient")->will($this->returnValue($patient));
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testIfCreateAccessAndPatientExistsButEncounterFormDoesNotExistNoOptionsAreCreated() {
        $page = $this->_page;
		
        $this->givenMockPatientIsSelected();
        
        $page->setPermission('create',true);
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(null));
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testIfCreateAccessAndPatientExistsButThereIsNoDomainAndEncounterFormExistsAddOptionIsCreated() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
       
        $page->setPermission('create',true);
        
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1,"type"=>$page->getSetting('encounter'))));
        $options = $page->createMenu();
        $testOpts = $this->addToDomain;
		$this->assertEquals($testOpts,strip_tags($options->output()));
    }
    
    public function testOnceCreatedAndNoChildPermissionsOptionsNotAvailable() {
        $page = $this->_page;
        $cpages = $page->getSettingList('create-set',',');
        $em = ADD::sys()->getEncounterManager();
        $this->givenMockPatientIsSelected();
        $page->setPermission('create',true);
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
		ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1,"type"=>$page->getSetting('encounter'))));
       
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
		$er = $this->getMock("ADD_EncounterRecord");
		$er->expects($this->any())->method("exists")->will($this->returnValue(true));
		
		foreach($this->_module->getPages() as $cpage) {
            $cencType = $cpage->getSetting('encounter');
			$em->setExpected($cencType,"child",$er);
			$em->setExpected($cencType,"record",$er);
		}
		 
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $options = $page->createMenu();
        $this->assertEquals($this->addedToDomain."No access to domain data",(string)strip_tags($options));
    }
    
    public function testOnceCreatedAndGivenPermissionsOptionsAreCreated() {
        $page = $this->_page;
        $em = ADD::sys()->getEncounterManager();
        $this->givenMockPatientIsSelected();
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1,"type"=>$page->getSetting('encounter'))));
       
        foreach($this->_module->getPages() as $mpage) {
            $mpage->setPermission('create',true);
        }
       
       	$er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        foreach($this->_module->getPages() as $cpage) {
            $cencType = $cpage->getSetting('encounter');
                $em->setExpected($cencType,"record",$er);
                $em->setExpected($cencType,"child",$er);
                $em->setExpected($cencType,"children",array());
        }
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $options = $page->createMenu();
        $expOpts = $this->addedToDomain;
        foreach($page->getChildPages() as $cpage) {
            $expOpts.=$cpage->createMenu();
        }
        
        $this->assertEquals(strip_tags($expOpts),(string)strip_tags($options));
    }
    
    public function setChildPermissions($page,$perm) {
        $cpages = $page->getChildPages();
        foreach($cpages as $cpage) {
           $cpage->setPermission($perm,true);
        }
    }
    
/*    
    public function testGenerateDoesNotStoreWhenErrors() {
        $er = $this->getMock("EncounterRecord",array("exists"));
        $page = $this->getMock("Page",array("registerToken","getErrors"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(true));
        $er->expects($this->once())->method("exists")->will($this->returnValue(TRUE));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($this->_page);
    }
    
    public function testGenerateDoesNotStoreWhenAlreadyExists() {
        $er = $this->getMock("EncounterRecord",array("exists"));
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        
        $er->expects($this->once())->method("exists")->will($this->returnValue(TRUE));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($this->_page);
    }
    
    public function testGenerateStoresWhenDoesNotExist() {
        $er = $this->getMock("EncounterRecord",array("exists","store"));
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList");
        
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($this->_page);
    }
    
    public function testGenerateStoresCreatesSubEncountersForChildList() {
        $mod = $this->getMock("Module",array("getPage"));
        $page2 = $this->getMock("Page",array("getSetting"));
        $page2->expects($this->exactly(3))->method("getSetting");
        $mod->expects($this->exactly(3))->method("getPage")->will($this->returnValue($page2));
        
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList","getModule"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("test1","test2","test3")));
        $page->expects($this->once())->method("getModule")->will($this->returnValue($mod));
        
        $cer = $this->getMock("EncounterRecord",array("store"));
        $cer->expects($this->exactly(3))->method("store");
        
        $er = $this->getMock("EncounterRecord",array("exists","store","newChild"));
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        $er->expects($this->exactly(3))->method("newChild")->will($this->returnValue($cer));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($this->_page);
    }
    
    public function testOptionsGeneration() {
        $mod = $this->getMock("Module",array("getPage"));
        $page2 = $this->getMock("Page",array("getSetting"));
        $page2->expects($this->exactly(3))->method("getSetting");
        $mod->expects($this->exactly(3))->method("getPage")->will($this->returnValue($page2));
        
        $page = $this->getMock("Page",array("registerToken","getErrors","getSetting","addMessage","redirectUsingSetting","getSettingList","getModule"));
        $page->expects($this->once())->method("registerToken")->will($this->returnValue(true));
        $page->expects($this->once())->method("getErrors")->will($this->returnValue(null));
        $page->expects($this->once())->method("addMessage");
        $page->expects($this->once())->method("redirectUsingSetting");
        $page->expects($this->once())->method("getSettingList")->will($this->returnValue(array("test1","test2","test3")));
        $page->expects($this->once())->method("getModule")->will($this->returnValue($mod));
        
        $cer = $this->getMock("EncounterRecord",array("store"));
        $cer->expects($this->exactly(3))->method("store");
        
        $er = $this->getMock("EncounterRecord",array("exists","store","newChild"));
        $er->expects($this->once())->method("exists")->will($this->returnValue(false));
        $er->expects($this->once())->method("store");
        $er->expects($this->exactly(3))->method("newChild")->will($this->returnValue($cer));
        $comp = new ADD_Component_Encounter_Domain_ManualEnrol();
        $comp->setEncounterRecord($er);
        $comp->generate($this->_page);
    } 
 * 
 */
}


?>
