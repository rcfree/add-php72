<?php

class ADD_Component_Encounter_StudyRepeatSection_OptionTest extends ADD_Test_EncounterBase {
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
	public function no_studies_menu_if_patient_not_interested_in_recruitment() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		$sent = ADD::sys()->getPageInModule('research','sent');
        
		$this->givenPageEncounterExistsAndHasValues(array("pat_sent_db_consent"=>"N"),$sent,"record");
		
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $options = $page->createMenu();
        $testOpts = "&nbsp;Not able to manage studiesPatient has no database consents";
		
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function warning_is_displayed_if_patient_interested_is_available_and_has_id_but_no_db_consent_records() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $options = $page->createMenu();
		
        $testOpts = "&nbsp;Not able to manage studiesPatient has no database consents";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function warning_is_displayed_if_patient_available_with_id_but_no_recruitment_db_permission() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("add_to_recruit_db"=>"N")
		),"research");
		$options = $page->createMenu();
        $testOpts = "&nbsp;Not able to manage studiesNo consent to be added to database";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function warning_is_displayed_if_was_recruitment_db_permission_but_is_not_now() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"Y"),
			array("consent_form_date"=>"02/06/2013","add_to_recruit_db"=>"N")
		),"research");
		$options = $page->createMenu();
        $testOpts = "&nbsp;Not able to manage studiesNo consent to be added to database";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function warning_is_displayed_if_multiple_none_recruitment_db_permissions() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"N"),
			array("consent_form_date"=>"02/06/2013","add_to_recruit_db"=>"N")
		),"research");
		$options = $page->createMenu();
        $testOpts = "&nbsp;Not able to manage studiesNo consent to be added to database";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function options_are_displayed_if_was_no_recruitment_db_permission_but_is_now() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/06/2013","add_to_recruit_db"=>"Y"),
			array("consent_form_date"=>"02/02/2013","add_to_recruit_db"=>"N")
		),"research");
		$options = $page->createMenu();
        $testOpts = "Study list (0)-select study-studylist1studylist2studylist3studylist4studylist5";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function options_are_displayed_if_multiple_recruitment_db_permissions() {
		$page = $this->_page;
        $this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus();
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/06/2013","add_to_recruit_db"=>"Y"),
			array("consent_form_date"=>"02/02/2013","add_to_recruit_db"=>"Y")
		),"research");
		$options = $page->createMenu();
        $testOpts = "Study list (0)-select study-studylist1studylist2studylist3studylist4studylist5";
        $this->assertEquals($testOpts,strip_tags($options->output()));
	}
	
	/**
	 * @test
	 */
	public function study_links_are_not_displayed_if_only_create_permissions() {
		$this->givenPatientHasStatusAndIsConsented();
		$this->_page->setPermissions(array('create'=>true));
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
			array("study"=>3),
		),"research_studies");
		
		$this->assertPageMenuEquals("Study list (2)You do not have permission to view studies-select study-studylist1studylist4studylist5");
	}
	
	/**
	 * @test
	 */
	public function study_section_is_not_displayed_if_only_view_permissions() {
		$this->givenPatientHasStatusAndIsConsented();
		$this->_page->setPermissions(array('view'=>true));
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
			array("study"=>3),
		),"research_studies");
		
		$this->assertPageMenuEquals("");
	}
	
	/**
	 * @test
	 */
	public function study_add_option_is_not_displayed_if_child_view_permissions() {
		$this->givenPatientHasStatusAndIsConsented();
		$this->_page->setPermissions(array('view'=>true));
		$this->givenUserChildPagePermissions("sdetails","view");
	
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
			array("study"=>3),
		),"research_studies");
		
		$this->assertPageMenuEquals("Study list (2)study2no study statusstudy3no study status");
	}
	
	
	/**
	 * @test
	 */
	public function study_links_are_displayed_with_status_and_date_if_full_permissions() {
		$this->givenPatientHasStatusAndIsConsented();
		$this->givenUserHasAccessToStudyList();
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2,"current_status"=>3,"enrol_date"=>"03/02/2013"),
		),"research_studies");
		
		$this->assertPageMenuEquals("Study list (1)study2current_status3 (03/02/2013)-select study-studylist1studylist3studylist4studylist5");
	}
	
	/**
	 * @test
	 */
	public function show_list_of_studies_but_no_add_if_patient_agrees_to_recruitment_but_is_not_suitable() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		$sent = ADD::sys()->getPageInModule('research','sent');
        
		$this->givenPatientHasStatusAndIsConsented(1);
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
		),"research_studies");
		
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$page->getChildPage("sdetails")->setPermission('create',true);
        $options = $page->createMenu();
        $this->assertPageMenuEquals("Study list (1)study2no study status&nbsp;Only manage existing studies Patient unsuitable for research");
    }
    
    /**
	 * @test
	 */
	public function show_list_of_studies_but_no_add_if_patient_agrees_to_recruitment_but_is_not_available() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		$sent = ADD::sys()->getPageInModule('research','sent');
        
		$this->givenPatientHasStatusAndIsConsented(5);
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2,"current_status"=>4,"end_date"=>"03/02/2013"),
		),"research_studies");
		
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$page->getChildPage("sdetails")->setPermission('create',true);
        $options = $page->createMenu();
        $this->assertPageMenuEquals("Study list (1)study2current_status4 (03/02/2013)&nbsp;Only manage existing studies Patient not available");
    }
	
	/**
	 * @test
	 */
	public function show_list_of_studies_but_no_add_if_patient_agrees_to_recruitment_but_status_is_do_not_contact() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		$sent = ADD::sys()->getPageInModule('research','sent');
        
		$this->givenPatientHasStatusAndIsConsented(2);
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
		),"research_studies");
		
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$page->getChildPage("sdetails")->setPermission('create',true);
        $options = $page->createMenu();
        $this->assertPageMenuEquals("Study list (1)study2no study status&nbsp;Not able to manage studies Patient requests no contact");
    }
	
	/**
	 * @test
	 */
	public function show_list_of_studies_but_no_add_if_patient_agrees_to_recruitment_but_is_deceased() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		$this->expectsGetPatientToReturnDeceasedPatient();
		
		$sent = ADD::sys()->getPageInModule('research','sent');
        $research = ADD::sys()->getPageInModule('research','research');
        $research->createMenu();
		$this->givenPatientHasStatusAndIsConsented(2);
		
		$this->expectChildrenWithPageValues("study","sdetails",array(
			array("study"=>2),
		),"research_studies");
		
		$page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$page->getChildPage("sdetails")->setPermission('create',true);
        $options = $page->createMenu();
		
        $this->assertPageMenuEquals("Study list (1)study2no study status&nbsp;Not able to manage studies Patient is deceased");
    }
	
	public function givenPatientHasStatusAndIsConsented($status=3) {
		$this->setupPatientAndParams();
		$this->givenPatientSentFormAndResearchStatus($status);
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/06/2013","add_to_recruit_db"=>"Y"),
		),"research");
	}
	
	public function givenUserHasAccessToStudyList() {
		$page = $this->_page;
        $page->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$this->givenUserChildPagePermissions("sdetails",array("view"=>true,"create"=>true,"edit"=>true));
	
	}
	
	public function assertPageMenuEquals($menuText) {
		$options = $this->_page->createMenu();
        $this->assertEquals($menuText,strip_tags($options->output()));
	}
	
	public function givenUserChildPagePermissions($childPageName,$permissions) {
		if(is_scalar($permissions)) $permissions=array($permissions=>true);
		$this->_page->getChildPage($childPageName)->setPermissions($permissions);
		
	}
	
	public function givenPatientSentFormAndResearchStatus($status=3) {
		$sent = ADD::sys()->getPageInModule('research','sent');
        $contact = ADD::sys()->getPageInModule('research','details');
		$this->givenPageEncounterExistsAndHasValues(array(ADD_Constants_Research::SENT_CONSENT_FIELD=>"Y"),$sent,"record");
		$this->givenPageEncounterExistsAndHasValues(array(ADD_Constants_Research::CURRENT_STATUS_FIELD=>$status,ADD_Constants_Research::ID_FIELD=>"ilh34534534"),$contact,"child");
	}
	
}

?>
