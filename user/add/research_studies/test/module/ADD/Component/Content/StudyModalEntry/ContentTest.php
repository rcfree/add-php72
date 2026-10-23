<?php

class ADD_Component_Content_StudyModalEntry_ContentTest extends ADD_Test_EncounterBase {
	public function testFormTitleIsCorrect() {
		$this->markTestIncomplete();
		$sdetails = ADD::sys()->getPageInModule('research_studies','sdetails');
		$this->givenPageEncounterExistsAndHasValues(array("study"=>2,"current_status"=>1),$sdetails);
		$this->assertEquals("Study actions for 'study2'",$this->_page->createTitle());
	}
	
	/**
	 * @test
	 */
	public function on_submit_add_warning_if_expected_study_status_is_not_defined() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		$cer->expects($this->never())->method("store");
		$this->_page->submitForm(array("action_type"=>"studyenrol","current_status"=>2));
		$this->assertHasWarning("Study status 'Enrolled' is not defined in ADD");
	}
	
	/**
	 * @test
	 */
	public function on_submit_set_study_status_to_enrolled_if_enrol_action_is_complete_and_study_allows_recruitment() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>3,"title"=>"Enrolled"
		)));
		
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("data" => ADD_Util::serialize(array("allow_recruitment"=>1))
		)));
		$detailsPage = $add->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->never())->method("store");
		
		$cer->expects($this->once())->method("store")->with(array("current_status"=>3,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->_page->submitForm(array("action_type"=>"studyenrol","current_status"=>2,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->assertHasMessages(array(
			"Patient's study status automatically changed by action to 'Enrolled', enrol date is 03/02/2013 and study number is S23423",
			"Study allows enrolment in other studies. Recruitment status has not been changed"
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_set_study_status_to_enrolled_if_enrol_action_is_complete_and_rec_status_to_unavailable_if_study_does_not_allow_recruitment() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>3,"title"=>"Enrolled"
		)));
		
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("data" => array("allow_recruitment"=>"0")
		)));
		
		$cer->expects($this->once())->method("store")->with(array("current_status"=>3,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		
		$detailsPage = $add->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->once())->method("store")->with(array("current_status"=>5));
		
		$this->_page->submitForm(array("action_type"=>"studyenrol","current_status"=>2,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->assertHasMessage("Patient's study status automatically changed by action to 'Enrolled', enrol date is 03/02/2013 and study number is S23423");
		$this->assertHasWarning("Study does not allow enrolment in other studies. Recruitment status has been changed to 'Not currently available'");
	}

	/**
	 * @test
	 */
	public function on_submit_do_not_set_study_status_if_enrol_action_is_not_as_expected() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Scheduled"
		)));
		
		$cer->expects($this->never())->method("store");
		$this->_page->submitForm(array("action_type"=>"studyenrol","current_status"=>2,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
	}
	const END_DATE="03/02/2013";
	const STUDY_NO="S23423";
	/**
	 * @test
	 */
	public function on_submit_set_study_status_to_withdrawn_with_date_if_withdraw_action_is_complete() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>3,"title"=>"Withdrawn"
		)));
		
		$cer->expects($this->once())->method("store")->with(array("current_status"=>3,"end_date"=>self::END_DATE));
		$this->_page->submitForm(array("action_type"=>"withdraw","current_status"=>2,"end_date"=>self::END_DATE));
		$this->assertHasMessage("Patient's study status automatically changed by action to 'Withdrawn' and end date set to ".self::END_DATE);
	}
	
	/**
	 * @test
	 */
	public function on_submit_do_not_set_study_status_if_already_set() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$cer->expects($this->any())->method("getObsValue")->will($this->returnValue(3));
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>3,"title"=>"Withdrawn"
		)));
		
		$cer->expects($this->never())->method("store");
		$this->_page->submitForm(array("action_type"=>"withdraw","current_status"=>2,"end_date"=>self::END_DATE));
		$this->assertHasMessage("Patient's study status already changed to 'Withdrawn'");
	}
	/**
	 * @test
	 */
	public function on_submit_set_study_status_to_withdrawn_if_withdraw_action_is_complete_and_rec_status_back_to_available_if_study_does_not_allow_recruitment() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>3,"title"=>"Withdrawn"
		)));
		
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("data" => array("allow_recruitment"=>"0")
		)));
		
		$cer->expects($this->once())->method("store")->with(array("current_status"=>3,"end_date"=>self::END_DATE));
		
		$detailsPage = $add->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		
		$this->_page->submitForm(array("action_type"=>"withdraw","current_status"=>2,"end_date"=>self::END_DATE));
		$this->assertHasMessage("Patient's study status automatically changed by action to 'Withdrawn' and end date set to 03/02/2013");
		$this->assertHasWarning("Study did not allow enrolment in other studies. Recruitment status has been changed to 'Available'");
	}

	/**
	 * @test
	
	public function on_submit_set_study_status_to_awaiting_enrolment_if_send_pis_is_scheduled() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>2));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$cer = $this->getERMock();
		$this->setExpectedER($this->_page->getSiblingPage('sdetails'),"child",$cer,$er);
		$this->_page->setPermission('create',true);
		$this->_page->setupComponents();
		
		ADD::db('dictionary')->expects($this->at(0))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>2,"title"=>"Completed"
		)));
		ADD::db('dictionary')->expects($this->at(1))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>1,"title"=>"Scheduled"
		)));
		
		ADD::db('dictionary')->expects($this->at(2))->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("value"=>7,"title"=>"Awaiting enrolment"
		)));
		
		ADD::db('dictionary')->expects($this->once())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("data" => ADD_Util::serialize(array("allow_recruitment"=>1))
		)));
		$detailsPage = $add->getPageInModule('research','details');
		$details = $detailsPage->getEncounterRecord();
		$details->expects($this->never())->method("store");
		
		$cer->expects($this->once())->method("store")->with(array("current_status"=>7));
		$this->_page->submitForm(array("action_type"=>"sendpis","current_status"=>2,"date_scheduled"=>self::END_DATE));
		$this->assertHasMessages(array(
			"Patient's study status automatically changed by action to 'Awaiting enrolment'",
		));
	}
	 * 
	 */
}

?>
