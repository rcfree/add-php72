<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Encounter_Domain_ManualEnrol_ContentTest extends ADD_Test_EncounterBase {
    
    
    /**
     * @expectedException ADD_PatientException
     * @expectedExceptionMessage The patient ID is null
     */
    public function testFailsIfNoPatient() {
        $page = $this->_page;
        $page->createContent();
    }
    
    /**
     * @expectedException ADD_AccessException
     * @expectedExceptionRegEx Sorry you do not have permission to add the patient to a .+
     */
    public function testFailsIfNoAccess() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $page->createContent();
    }
   
     /**
     * @expectedException ADD_ComponentException
     * @expectedExceptionRegEx It appears that you may have tried to add this patient to the '.+' domain multiple times. They have already been added to this domain.
     */
     public function testDatabaseCallsCorrectIfTokenAndCreateAccessAndExistingCurrentDomain() {
        $page = $this->_page;
       
		$this->givenMockPatientIsSelected();
		$this->givenParamIsSet("t","TOKEN123");
		$this->givenTokenExists();
       	$er = $this->getMock('ADD_EncounterRecord');
		
		$er->expects($this->never())
            ->method("store");
			ADD::sys()->getEncounterManager()->setExpected(0,"record",$er);
       
        $page->setPermission('create',true);
        $page->createContent();
	 }  
    
       
    public function testIfCreateAccessButNoOtherAccessTokenSetupButNoRedirect() {
        $page = $this->_page;
		$this->givenMockPatientIsSelected();
        $this->givenParamIsSet("t","TOKEN123");
       
        $this->givenTokenDoesNotExist();
        $this->_confirmCreateDomain();
	$page->setPermission('create',true);
        $content = $page->createContent();
        $this->assertHasNoWarnings();      
        $this->assertHasMessage($page->getSetting('add_message',"Patient was added to the '".$page->getSetting('title')."' domain successfully"));        
        $this->assertHasContent($content);
    }
    
    public function testIfCreateAccessAndOtherAccessTokenSetupWillRedirect() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $this->givenParamIsSet("t","TOKEN123");
        $this->givenTokenDoesNotExist();
        $this->_confirmCreateDomain();
		
        $page->setPermission('create',true);
        
        $redirect = $page->getSetting('redirect');
        ADD::log()->error("redirect:$redirect");
        if ($redirect) {
            $rpage =  $this->getMockBuilder('ADD_Page')->disableOriginalConstructor()->setMethods(array('getName','setModule','setPermission','getPermission','createMenuUri'))->getMock();
            $rpage->expects($this->exactly(1))->method("getName")->will($this->returnValue($redirect));
            $rpage->expects($this->exactly(1))->method("getPermission")->will($this->returnValue(true));
            $rpage->expects($this->exactly(1))->method("createMenuUri")->will($this->returnValue($redirect));
            
            $this->_module->getPages(true)->add($rpage);
            $rpage->setPermission('create',true);
        }
        $content = $page->createContent();
        $this->assertHasNoWarnings();      
        $this->assertHasMessage($page->getSetting('add_message',"Patient was added to the '".$page->getSetting('title')."' domain successfully"));      
        
        if ($redirect) $this->assertEquals($redirect,ADD::sys()->hasState('redirect'));
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
        ADD::db('patient')->expects($this->exactly(1))->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(null));
        $options = $page->createMenu();
        $this->assertEquals("",$options);
    }
    
    public function testOnceCreatedAndGivenPermissionsOptionsAreCreated() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
		
        $page->setPermission('create',true);
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1,"type"=>$page->getSetting('encounter'))));
        
        $er = $this->getMock('ADD_EncounterRecord');
		$er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $this->_em->setExpected($page->getSetting('encounter'),"record",$er);
		
        $cpages = $page->getChildPages();
        
        foreach($cpages as $cpage) {
           $cpage = $this->getMock('Page');
        }
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $options = $page->createMenu();
    }
    
    public function setChildPermissions($page,$perm) {
        $cpages = $page->getChildPages();
        foreach($cpages as $cpage) {
           $cpage->setPermission($perm,true);
        }
    }
    public function prepareERContentMock($page) {
        $page = $this->_page;
        $cpages = $page->getSettingList('create-set',",");
        $ccount = count($cpages);
        $erMock = $this->_erMock;
        $erMock->expects($this->any())->method("store");
        
        $cerMock = $this->getMock("ADD_EncounterRecord");
        $erMock->expects($this->exactly($ccount))->method("newChild")->will($this->returnValue($cerMock));
        $cerMock->expects($this->exactly($ccount))->method("store");
        return $erMock;
    }
    
    public function assertHasContent($content,$name="main") {
        $this->assertEquals(ADD::web()->getContent($name)->output(),$content);
    }
    public function assertHasWarning($warning) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        
        $this->assertEquals(array($warning),ADD::web()->getWarnings());
    }
    
     public function assertHasNoWarnings() {
        $this->assertTrue(ADD_Util::isEmpty(ADD::web()->getWarnings()));
    }
    
    public function assertHasMessage($message) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        
        $this->assertEquals(array($message),ADD::web()->getMessages());
    }
  
}


?>
