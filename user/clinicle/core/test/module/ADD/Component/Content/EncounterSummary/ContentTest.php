<?php

$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Component_Content_EncounterSummary_ContentTest extends ADD_Test_EncounterBase {
          public function testFormTitleIsCorrect() {
    $this->assertEquals($this->_page->getSetting('title'),$this->_page->createTitle());
}      
        public function testFormDisplayedIfViewPermission() {
                $page = $this->_page;
                $em = ADD::sys()->getEncounterManager();

                $this->givenMockPatientIsSelected();
                $cer = $this->getERMock();

                $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));

                $cer->expects($this->any())->method("exists")->will($this->returnValue(false));
                $em->setExpected($page->getSetting('encounter'),"child",$cer);
                $patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
                $page->setPermission('view',true);
                $page->setupComponents();
                $page->createContent();
                $this->assertGreaterThan(0,strlen(ADD::web()->getContent()));
         }
                 
}
