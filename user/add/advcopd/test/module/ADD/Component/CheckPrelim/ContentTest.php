<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Component_CheckPrelim_ContentTest extends ADD_Test_EncounterBase {
        public function setUp() {
            parent::setUp();
            $this->loadTestConfig();
            
        }
	function testCreateContentIfDoesNotExist() {
            $page = $this->_page;
            ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
        	
            $this->givenMockPatientIsSelected();
            $page->setPermission('create',true);
            $this->_page->createContent();
            $this->assertHasNoWarnings();
        }
        
        function testEditContentIfDoesExist() {
            $page = $this->_page;
            ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
        	
            $this->givenMockPatientIsSelected();
            $page->setPermission('edit',true);
            
            $er = $this->getERMock();
            $er->expects($this->any())->method("exists")->will($this->returnValue(true));
            ADD::sys()->getEncounterManager()->setExpected($page,"child",$er);
            $this->_page->createContent();
            $this->assertHasWarnings(array("All required observations have been entered. The record should be locked to prevent changes as soon as possible.","Patient DOES NOT fulfill criteria for advanced COPD clinic"));
        }
        static $_expConfig;
        function testEditContentWithExampleData() {
            $page = $this->_page;
            ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
        	
            $this->givenMockPatientIsSelected();
            $page->setPermission('edit',true);
            self::$_expConfig = $this->_conf->getSection('FormSubmitWithExampleData');
           
            $er = $this->getERMock();
            $er->expects($this->any())->method("exists")->will($this->returnValue(true));
            $er->expects($this->any())->method("getObsValue")->will(
            $this->returnCallback(function($name) {
                    return ADD_Component_CheckPrelim_ContentTest::$_expConfig->getSetting($name);
            }
            ));
            ADD::sys()->getEncounterManager()->setExpected($page,"child",$er);
            $this->_page->createContent();
            $this->assertHasWarning("All required observations have been entered. The record should be locked to prevent changes as soon as possible.");
            $this->assertHasMessage("Patient DOES fulfill criteria for advanced COPD clinic");
        }
}
?>