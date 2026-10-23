<?php

class ADD_Component_CheckDeceased_ContentTest extends ADD_Test_EncounterBase {
	public function setupPatientAndParams() {
		$page = $this->_page;
        $this->givenParamIsSet("t","TOKEN123");
        //$this->givenTokenDoesNotExist();
		$this->givenSelectedPageIs("");
		$this->givenMockPatientIsSelected();
		//$this->expectsGetPatientToReturnPatient();
		//$this->expectsGetEncounterFormWithTypeToReturnItem($page->getSetting('encounter'));
	}

	/**
	 * @test
	 */
	public function rec_db_patient_status_changed_to_deceased_if_patient_is_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnDeceasedPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$er = $page->getEncounterRecord();
		$er->expects($this->any())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(5));
		$er->expects($this->any())->method("getObsValues")->will($this->returnValue(array("current_status"=>5)));
		
		$page->setupComponents();
		
		$page->createContent();
		$form = $page->getComponent('Form');
		
		$this->assertEquals(6,$form->getField('current_status')->getValue());
        $this->assertHasWarning("Patient recruitment status has been automatically set to 'Patient is deceased'");
	}
	
	/**
	 * @test
	 */
	public function patient_status_not_changed_to_deceased_if_recruit_status_is_not_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));

		$er = $page->getEncounterRecord();
		$er->expects($this->any())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(5));
		
		$page->setupComponents();
		$page->createContent();
		
		$this->assertHasNoWarnings();
	}
	
	/**
	 * @test
	 */
	public function patient_status_not_changed_to_deceased_if_already_deceased_and_recruit_status_is_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnDeceasedPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$page->setupComponents();
		$er = $page->getEncounterRecord();
		$er->expects($this->any())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(6));
		
		$page->createContent();
		$this->assertHasNoWarnings();
	}
	
}

?>
