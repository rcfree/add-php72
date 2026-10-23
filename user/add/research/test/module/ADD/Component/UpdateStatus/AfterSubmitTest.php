<?php

class ADD_Component_UpdateStatus_AfterSubmitTest extends ADD_Test_EncounterBase {
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
	public function status_changed_to_not_currently_available_if_latest_recruit_db_is_no_and_patient_alive() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		//$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"N"),
		),"research");
		
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->once())->method("store")->with(array("current_status"=>5));
				
		$page->afterSubmit($page);
        $this->assertHasWarning("Using the latest consent record (dated 01/02/2013) the recruitment status has been automatically set to 'Not currently available'.");
		
	}
	
	/**
	 * @test
	 */
	public function status_changed_to_deceased_if_patient_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnDeceasedPatient();
		//$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"N"),
		),"research");
		
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->once())->method("store")->with(array("current_status"=>6));
				
		$page->afterSubmit($page);
        $this->assertHasWarning("Patient recruitment status has been automatically set to 'Patient is deceased'");
	}
	
	/**
	 * @test
	 */
	public function status_changed_to_available_if_alive_latest_recruit_db_is_yes_and_patient_alive() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnPatient();
		//$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"Y"),
		),"research");
		
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->once())->method("store")->with(array("current_status"=>3));
				
		$page->afterSubmit($page);
        $this->assertHasWarning("Using the latest consent record (dated 01/02/2013) the recruitment status has been automatically set to 'Available for recruitment'.");
	}
	
	
}

?>
