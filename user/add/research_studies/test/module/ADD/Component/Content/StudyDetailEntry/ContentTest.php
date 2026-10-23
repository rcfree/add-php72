<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/core/test/module");
require_once ("ADD/Component/Content/EncounterEntry/ContentTest.php");

class ADD_Component_Content_StudyDetailEntry_ContentTest extends ADD_Component_Content_EncounterEntry_ContentTest {
	const END_DATE="03/02/2013";
	const STUDY_NO="S23423";
	public $studyDetails;
	
	public function expectStudyExists() {
		$add = ADD::sys();
		$this->givenTokenDoesNotExist();
		$this->givenMockPatientIsSelected();
		$studies = array(array("study"=>2,"current_status"=>1));
		$per = $this->getERMock();
		$this->setExpectedER($add->getPageInModule('research','research'),"record",$per);
		$er = $this->getERMock();
		$this->setExpectedER($this->_page->getParent(),"child",$er,$per);
		$this->studyDetails = $this->getERMock();
		$this->setExpectedER($this->_page,"child",$this->studyDetails,$er);
		$this->_page->setPermissions(array('create'=>true,'edit'=>true));
		$this->_page->setupComponents();
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("title"=>"Dictionary item","data"=>null,"value"=>1)
		));
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemByTitle")->will($this->returnValue(
				array("title"=>"Dictionary item","data"=>null,"value"=>1)
		));
	}
	public function testFormTitleIsCorrect() {
		$this->givenPageEncounterExistsAndHasValues(array("study"=>2,"current_status"=>1),$this->_page);
		$this->assertEquals("Study details for 'study2'",$this->_page->createTitle());
	}
	
	/**
	 * @test
	 */
	public function on_submit_set_rec_status_to_unavailable_if_study_does_not_allow_recruitment_and_status_is_enrolled() {
		$this->expectStudyExists();
		$map = array(array("current_status",$this->_page,"enrolled"));
		$this->studyDetails->expects($this->once())->method("store")->with(array("current_status"=>3,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->once())->method("getObsText")->will($this->returnValueMap($map));
		
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		$researchDetails->expects($this->once())->method("store")->with(array("current_status"=>5));
		
		
		$this->_page->submitForm(array("current_status"=>3,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasWarning("Study does not allow enrolment in other studies. Recruitment status has been changed to 'Not currently available'");
		$this->assertHasMessages(array(
			"Created and saved new 'Study details' record.",
			"Automatically added the 'Dictionary item' action due to the change in study status to 'Enrolled'"
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_do_not_set_rec_status_if_status_is_already_unavailable() {
		$this->expectStudyExists();
		$map = array(array("current_status",$this->_page,"Enrolled"));
		$this->studyDetails->expects($this->never())->method("store")->with(array("current_status"=>2,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->any())->method("exists")->will($this->returnValue(true));
		
		$researchMap = array( array("current_status",5) );
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		
		$researchDetails->expects($this->once())->method("getObsValue")->will($this->returnValueMap($researchMap));
		$researchDetails->expects($this->never())->method("store")->with(array("current_status"=>5));
		
		$this->_page->submitForm(array("current_status"=>2,"enrol_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasNoWarnings();
		$this->assertHasMessages(array(
			"No changes were made to 'Study details' record.",
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_do_not_set_rec_status_if_status_is_already_complete() {
		$this->expectStudyExists();
		$map = array(array("current_status",$this->_page,"Study complete"));
		$this->studyDetails->expects($this->never())->method("store")->with(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->any())->method("exists")->will($this->returnValue(true));
		
		$researchMap = array( array("current_status",3) );
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		
		$researchDetails->expects($this->once())->method("getObsValue")->will($this->returnValueMap($researchMap));
		$researchDetails->expects($this->never())->method("store")->with(array("current_status"=>3));
		
		$this->_page->submitForm(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasNoWarnings();
		$this->assertHasMessages(array(
			"No changes were made to 'Study details' record.",
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_set_rec_status_to_available_if_study_does_not_allow_recruitment_and_status_is_complete() {
		$this->expectStudyExists();
		$map = array(array("current_status",$this->_page,"Study complete"));
		$this->studyDetails->expects($this->once())->method("store")->with(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->once())->method("getObsText")->will($this->returnValueMap($map));
		
		$researchMap = array( array("current_status",5) );
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		
		$researchDetails->expects($this->once())->method("getObsValue")->will($this->returnValueMap($researchMap));
		$researchDetails->expects($this->once())->method("store")->with(array("current_status"=>3));
		
		$this->_page->submitForm(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasWarning("Study did not allow enrolment in other studies. Recruitment status has been changed to 'Available'");
		$this->assertHasMessages(array(
			"Created and saved new 'Study details' record.",
			"Automatically added the 'Dictionary item' action due to the change in study status to 'Study complete'"
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_do_not_set_rec_status_but_do_add_action_if_new_study_allows_recruitment() {
		$this->expectStudyExists();
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("title"=>"Dictionary item","data" => array("allow_recruitment"=>"1")
		)));
		
		$map = array(array("current_status",$this->_page,"Study complete"));
		$this->studyDetails->expects($this->once())->method("store")->with(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->once())->method("getObsText")->will($this->returnValueMap($map));
		
		$researchMap = array( array("current_status",5) );
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		
		$researchDetails->expects($this->once())->method("getObsValue")->will($this->returnValueMap($researchMap));
		$researchDetails->expects($this->once())->method("store")->with(array("current_status"=>3));
		
		$this->_page->submitForm(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasMessages(array(
			"Created and saved new 'Study details' record.",
			"Automatically added the 'Dictionary item' action due to the change in study status to 'Study complete'"
		));
	}
	
	/**
	 * @test
	 */
	public function on_submit_do_not_change_rec_status_or_add_activity_if_old_study_allows_recruitment() {
		$this->expectStudyExists();
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemWithValue")->will($this->returnValue(
				array("title"=>"Dictionary item","data" => array("allow_recruitment"=>"1")
		)));
		
		$map = array(array("current_status",$this->_page,"Study complete"));
		$this->studyDetails->expects($this->never())->method("store")->with(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		$this->studyDetails->expects($this->any())->method("exists")->will($this->returnValue(true));
		
		$researchMap = array( array("current_status",5) );
		$researchDetailsPage = ADD::sys()->getPageInModule('research','details');
		$researchDetails = $researchDetailsPage->getEncounterRecord();
		
		$researchDetails->expects($this->once())->method("getObsValue")->will($this->returnValueMap($researchMap));
		$researchDetails->expects($this->never())->method("store")->with(array("current_status"=>3));
		
		$this->_page->submitForm(array("current_status"=>8,"end_date"=>self::END_DATE,"study_no"=>self::STUDY_NO));
		ADD::web()->afterSubmit($this->_page);
		$this->assertHasMessages(array(
			"No changes were made to 'Study details' record."
		));
	}
}

?>
