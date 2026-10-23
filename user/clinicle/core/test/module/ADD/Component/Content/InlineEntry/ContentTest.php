<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Content_EncounterEntry_ContentTest extends ADD_Test_EncounterBase {
        protected $_conf = null;
        public function setUp() {
                parent::setUp();
                ADD::db('dictionary')->expects($this->any())->method("hasDictionary")->will($this->returnValue(true));
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3"); }));
	    	ADD::db('dictionary')->expects($this->any())->method("getDictionaryGroups")->will($this->returnCallback(function($name) { return 
                    array(
                        array("name"=>$name."1","title"=>"Group ".$name." 1"),
                        array("name"=>$name."2","title"=>"Group ".$name." 2"),
                        array("name"=>$name."3","title"=>"Group ".$name." 3"),
                );}
          ));

        //$this->loadTestConfig();
        }
        public function testFormTitleIsCorrect() {
            $this->assertEquals($this->_page->getSetting('title'),$this->_page->createTitle());
        }
        public function testFormDisplayedIfViewPermission() {
                $page = $this->_page;
                $em = ADD::sys()->getEncounterManager();

                $this->givenMockPatientIsSelected();
                $this->givenPageEncounterExistsAndHasValues(array(),$page->getParent());
                $page->setPermission('view',true);
                $page->setupComponents();
                $page->createContent();
                $this->assertGreaterThan(0,strlen(ADD::web()->getContent()));
         }
         
         public function testFormSubmittedIfEditPermission() {
                $page = $this->_page;
                $em = ADD::sys()->getEncounterManager();

                $this->givenMockPatientIsSelected();
                $this->givenPageEncounterExistsAndHasValues(array(),$page->getParent());
                //$cer = $this->getERMock();

                //$cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));

                //$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
                //$em->setExpected($page->getSetting('encounter'),"child",$cer);
                $patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
                $page->setPermission('edit',true);
                $page->setupComponents();
                $page->submitForm(array());
                $page->createContent();
                $this->assertGreaterThan(0,strlen(ADD::web()->getContent()));
         }

/*
        public function testEditedContentContainsCorrectData() {

                $page = $this->_page;
                $em = ADD::sys()->getEncounterManager();
                ADD::sys()->setParam('t','TOKEN');
                $this->givenMockPatientIsSelected();
                $cer = $this->getMock('ADD_EncounterRecord');
                $this->_expConfig = $this->_conf->getSection('FormSubmitWithExampleData')->getConfig();

                $cer->expects($this->any())->method("exists")->will($this->returnValue(true));
                $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
                $cer->expects($this->any())->method("getObsValues")->will($this->returnValue($this->_expConfig));

                $em->setExpected($page->getSetting('encounter'),"child",$cer);
                ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
                ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3","4"=>$name); }));


                $page->setPermission('edit',true);
                $page->setupComponents();
                $page->createContent();
                $html = ADD::web()->getContent();
                $form = $page->getComponent('Form');
                foreach($this->_expConfig as $name => $value) {
                    $this->assertEquals($value,$form->getField($name)->getValue(),"Field match '$name'");
                }
                $this->assertHasNoWarnings();
        }
 * 
 */
}
?>	