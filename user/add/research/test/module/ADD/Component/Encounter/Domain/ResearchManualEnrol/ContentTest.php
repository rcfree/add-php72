<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/core/test/module");
require_once ("ADD/Component/Encounter/Domain/ManualEnrol/ContentTest.php");

class ADD_Component_Encounter_Domain_ResearchManualEnrol_ContentTest extends ADD_Component_Encounter_Domain_ManualEnrol_ContentTest {
   public function testOnceCreatedAndGivenPermissionsOptionsAreCreated() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
		
        $page->setPermission('create',true);
        $patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->will($this->returnValue($patient));
        ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1,"type"=>$page->getSetting('encounter'))));
        
        $er = $this->getMock('ADD_EncounterRecord');
		$er->expects($this->exactly(3))->method("exists")->will($this->returnValue(true));
        $this->_em->setExpected($page->getSetting('encounter'),"record",$er);
		
        $cpages = $page->getChildPages();
        
        foreach($cpages as $cpage) {
           $cpage = $this->getMock('Page');
        }
        ADD::sys()->getAccessManager()->setCurrentPage($page);
        $options = $page->createMenu();
    }
    
}


?>
