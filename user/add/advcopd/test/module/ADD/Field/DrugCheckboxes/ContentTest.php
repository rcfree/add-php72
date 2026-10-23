<?php
set_include_path(getenv('ADD_PATH')."/test/module");

class ADD_Field_DrugCheckboxes_ContentTest extends ADD_Test_EncounterBase {
        function setUp() {
            parent::setUp();
            
            $this->_page->setupComponents();
            $form = $this->_page->getComponent('Form');
            $this->_field = $form->getField(getenv('ADD_FIELD_TEST'));
      
      
	}
	function testCreatesMessageIfNoDrugsRecords() {
		$page = $this->_page;

		$this->givenMockPatientIsSelected();
		$page->setPermission('create',true);
		$this->_field->finaliseField($this->_page);
		$this->_field->createMarkup($this->_page);
		$this->assertHasTextContent($this->_field->getTitle()."Please add one or more drugs to the Drugs list in the Medications module");
		$this->assertHasNoWarnings();
	}
	function testCreatesListIfOneOrMoreDrugRecords() {
            $page = $this->_page;
            $encs = array(
                array("id"=>1,"patient_id"=>123),
                array("id"=>2,"patient_id"=>123),
                array("id"=>3,"patient_id"=>123)
            );
            
            ADD::db('encounter')->expects($this->exactly(1))->method("getEncountersWithPatientAndType")->will($this->returnValue($encs));
            $map = array();
            $encObs = explode(";",$this->_field->getSetting('encounter_obs'));
            foreach($encs as $enc) {
                $item = array($enc,null);
                $obs = array();
                foreach($encObs as $encOb) {
                    $obs[$encOb]=$encOb.$enc['id'];
                }
                $item[]=$obs;
                $map[]=$item;
            }
			
            ADD::db('encounter')->expects($this->exactly(3))->method("getObservationsInEncounter")->will($this->returnValueMap($map));
            $this->givenMockPatientIsSelected();
            $page->setPermission('create',true);
            $this->_field->finaliseField($this->_page);
			print_r($this->_field);
            $this->_field->createMarkup($this->_page);
            $this->assertHasTextContent($this->_field->getTitle()."drug_name1 formulation1 strength1drug_name2 formulation2 strength2drug_name3 formulation3 strength3");
            $this->assertHasNoWarnings();
        }
        function testCreatesListIfDrugRecordsInactive() {
            $page = $this->_page;
            $encs = array(
                array("id"=>1,"patient_id"=>123),
                array("id"=>2,"patient_id"=>123),
                array("id"=>3,"patient_id"=>123)
            );
            
            ADD::db('encounter')->expects($this->exactly(1))->method("getEncountersWithPatientAndType")->will($this->returnValue($encs));
            $map = array();
            $encObs = explode(";",$this->_field->getSetting('encounter_obs'));
            foreach($encs as $enc) {
                $item = array($enc,null);
                $obs = array();
                foreach($encObs as $encOb) {
                    $obs[$encOb]=$encOb.$enc['id'];
                }
                $obs['current_status']=2;
                $item[]=$obs;
				
                $map[]=$item;
            }
            ADD::db('encounter')->expects($this->exactly(3))->method("getObservationsInEncounter")->will($this->returnValueMap($map));
            $this->givenMockPatientIsSelected();
            $page->setPermission('create',true);
            $this->_field->finaliseField($this->_page);
			print_r($this->_field);
            $this->_field->createMarkup($this->_page);
            $this->assertHasTextContent($this->_field->getTitle()."INACTIVE (drug_name1 formulation1 strength1)INACTIVE (drug_name2 formulation2 strength2)INACTIVE (drug_name3 formulation3 strength3)");
            $this->assertHasNoWarnings();
        }
        
}
?>