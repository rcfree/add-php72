<?php
class ADD_Component_CheckDeceased_AfterSubmitTest extends ADD_Test_EncounterBase {
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
	public function alive_patient_status_changed_to_deceased_if_recruit_status_is_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		ADD::db()->expects($this->once())->method("update")->with("patient", 123,array("deceased"=>1),"date_updated");
	
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();	
		$details->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(6));
		
		$page->afterSubmit($page);
        $this->assertHasWarning("Patient recruitment status is deceased so their demographics status has also been changed.");
	}
	
	/**
	 * @test
	 */
	public function patient_status_not_changed_to_deceased_if_already_deceased_and_recruit_status_is_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnDeceasedPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		ADD::db()->expects($this->never())->method("update");
	
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();	
		$details->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(6));
		
		$page->afterSubmit($page);
        $this->assertHasNoWarnings();
	}
	
	/**
	 * @test
	 */
	public function patient_status_not_changed_to_deceased_if_recruit_status_is_not_deceased() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnPatient();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));

		ADD::db()->expects($this->never())->method("update");
	
		$detailsPage = ADD::sys()->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();	
		$details->expects($this->once())->method("getObsValue")->with("current_status")->will($this->returnValue(5));
		
		$page->afterSubmit($page);
        $this->assertHasNoWarnings();
	}
	
}

?>
