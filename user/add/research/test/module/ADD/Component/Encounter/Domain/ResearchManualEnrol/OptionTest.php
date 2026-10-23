<?php
$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");
require_once ("ADD/Component/Encounter/Domain/ManualEnrol/OptionTest.php");

class ADD_Component_Encounter_Domain_ResearchManualEnrol_OptionTest extends ADD_Component_Encounter_Domain_ManualEnrol_OptionTest {
	public function setUp() {
		parent::setUp();
		$this->addToDomain="Research&nbsp;Not sent&nbsp;Recruit patient to respiratory research";
		$this->addedToDomain="Research*Status and consent not setup";
	}
	
	public function setupPatientAndParams() {
		$page = $this->_page;
        $this->givenParamIsSet("t","TOKEN123");
        //$this->givenTokenDoesNotExist();
		$this->givenSelectedPageIs("");
		$this->givenMockPatientIsSelected();
		$this->expectsGetPatientToReturnPatient();
		$this->expectsGetEncounterFormWithTypeToReturnItem($page->getSetting('encounter'));
	}
	
	/**
	 * @test
	 */
	public function prelim_options_are_displayed_when_not_added_to_domain_and_no_create_domain_access() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        
        $options = $page->createMenu();
        $testOpts = "Research&nbsp;Not sentRequest for consentRecord of consent request";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function no_options_are_displayed_if_patient_is_deceased_and_not_added_to_research_domain() {
        $page = $this->_page;
        $page = $this->_page;
        $this->givenParamIsSet("t","TOKEN123");
        //$this->givenTokenDoesNotExist();
		$this->givenSelectedPageIs("");
		$this->givenMockPatientIsSelected();
		$this->expectsGetPatientToReturnDeceasedPatient();
		$this->expectsGetEncounterFormWithTypeToReturnItem($page->getSetting('encounter'));
		
        $module = $this->_module;
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        
        $options = $page->createMenu();
        $testOpts = "Research&nbsp;DeceasedRequest for consentRecord of consent request";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function prelim_options_are_displayed_along_with_create_option_when_not_added_to_domain_if_create_domain_access() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
        
		
        $options = $page->createMenu();
        $testOpts = "Research&nbsp;Not sentRequest for consentRecord of consent request&nbsp;Recruit patient to respiratory research";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function prelim_options_are_displayed_along_with_warning_when_added_to_domain_if_create_domain_and_create_prelim_access_but_no_other_access() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
		
        $options = $page->createMenu();
        $testOpts = "Research*Status and consent not setupRequest for consentRecord of consent requestNo access to other domain data";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function all_options_are_displayed_when_added_to_domain_if_create_domain_and_details_access_but_status_not_set() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
		$module->getPage('details')->setPermissions(array('create'=>true));
        
		
        $options = $page->createMenu();
        $testOpts = "Research*Status and consent not setupRequest for consentRecord of consent request&nbsp;Add patient to contact details section..";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function options_displayed_with_no_access_to_db_consent_when_in_domain_if_create_domain_and_other_options_but_status_not_set() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
		$module->getPage('details')->setPermissions(array('create'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
        
		
        $options = $page->createMenu();
        $testOpts = "Research*Status and consent not setupRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;Research status not set";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function only_prelim_options_displayed_with_no_access_to_db_consent_when_not_in_domain_and_not_interested_in_db() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$sent = $module->getPage('sent');
		$sent->setPermissions(array('create'=>true));
		$this->givenPageEncounterExistsAndHasValues(array("pat_sent_db_consent"=>"N"),$sent,"record");
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
		$module->getPage('details')->setPermissions(array('create'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
        
		
        $options = $page->createMenu();
        $testOpts = "Research&nbsp;No interestRequest for consentRecord of consent request&nbsp;Patient not interested";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function only_prelim_options_displayed_with_no_access_to_db_consent_when_in_domain_and_not_interested_in_db() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$sent = $module->getPage('sent');
		$details = $module->getPage('details');
		
		$this->givenPageEncounterExistsAndHasValues(array("pat_sent_db_consent"=>"N"),$sent,"record");
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>3,"ilh_identifier"=>"ILH23423423"),$details);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $module->getPage('research')->setPermissions(array('create'=>true));
		$module->getPage('details')->setPermissions(array('edit'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
		
        $options = $page->createMenu();
        $testOpts = "Research*&nbsp;No interestRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;Enrolled in research but marked as not interested";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function options_displayed_with_access_to_db_consent_when_in_domain_and_other_options_when_status_available_and_id() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $details = $module->getPage('details');
		$details->setPermissions(array('create'=>true));
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>3,"ilh_identifier"=>"ILH23423423"),$details);
        $module->getPage('general')->setPermissions(array('create'=>true));
     		
        $options = $page->createMenu();
        $testOpts = "Research*Consent not setupRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;New database consent..";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function options_displayed_with_access_to_db_consent_when_in_domain_and_other_options_when_status_not_available_and_id() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $details = $module->getPage('details');
		$details->setPermissions(array('create'=>true));
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>5,"ilh_identifier"=>"ILH23423423"),$details);
        $module->getPage('general')->setPermissions(array('create'=>true));
     		
        $options = $page->createMenu();
        $testOpts = "Research*Consent not setupRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;New database consent..";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function options_displayed_with_access_to_db_consent_when_in_domain_and_other_options_when_status_is_unsuitable() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $details = $module->getPage('details');
		$details->setPermissions(array('create'=>true));
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>1,"ilh_identifier"=>"ILH23423423"),$details);
        $module->getPage('general')->setPermissions(array('create'=>true));
     		
        $options = $page->createMenu();
        $testOpts = "Research*current_status1Request for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;New database consent..&nbsp;Unsuitable for recruitment";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function options_displayed_with_access_to_db_consent_when_in_domain_and_other_options_when_patient_requests_no_contact() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
        $details = $module->getPage('details');
		$details->setPermissions(array('create'=>true));
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>2,"ilh_identifier"=>"ILH23423423"),$details);
        $module->getPage('general')->setPermissions(array('create'=>true));
     		
        $options = $page->createMenu();
        $testOpts = "Research*current_status2Request for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;New database consent..&nbsp;Requests no contact";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function only_prelim_options_displayed_with_no_access_to_db_consent_when_in_domain_and_no_ilh_identifier() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$sent = $module->getPage('sent');
		$details = $module->getPage('details');
		
		$this->givenPageEncounterExistsAndHasValues(array("pat_sent_db_consent"=>"Y"),$sent,"record");
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>3,"ilh_identifier"=>""),$details);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$module->getPage('details')->setPermissions(array('edit'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
		
        $options = $page->createMenu();
        $testOpts = "Research*Consent not setupRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (0)&nbsp;Research/ILH identifier not setup";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function status_displayed_in_title_if_no_consent_request_but_consented_to_db() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$sent = $module->getPage('sent');
		$details = $module->getPage('details');
		
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>3,"ilh_identifier"=>"ilh1234567"),$details);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$module->getPage('details')->setPermissions(array('edit'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
		$module->getPage('consent')->setPermissions(array('create'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"Y"),
		),"research");
		
        $options = $page->createMenu();
        $testOpts = "Research*current_status3Request for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (1)&raquo;&nbsp;database consent 1 (consent_form_date01/02/2013)&nbsp;New database consent..";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
	
	/**
	 * @test
	 */
	public function not_consented_displayed_in_title_if_no_consent_request_and_not_consented_to_db() {
        $page = $this->_page;
        $this->setupPatientAndParams();
		
        $module = $this->_module;
		$this->givenDomainEncounterExists($page);
		
		$sent = $module->getPage('sent');
		$details = $module->getPage('details');
		
		$this->givenPageEncounterExistsAndHasValues(array("current_status"=>3,"ilh_identifier"=>"ilh1234567"),$details);
		
		$module->getPage('sent')->setPermissions(array('create'=>true,'edit'=>true,'view'=>true));
		$module->getPage('details')->setPermissions(array('edit'=>true));
        $module->getPage('general')->setPermissions(array('create'=>true));
		$module->getPage('consent')->setPermissions(array('create'=>true));
		
		$this->expectChildrenWithPageValues("general","consent",array(
			array("consent_form_date"=>"01/02/2013","add_to_recruit_db"=>"N"),
		),"research");
		
        $options = $page->createMenu();
        $testOpts = "Research*No consent to be added to databaseRequest for consentRecord of consent request&nbsp;Add patient to contact details section..Database consent (1)&raquo;&nbsp;database consent 1 (consent_form_date01/02/2013)&nbsp;New database consent..";
        $this->assertEquals($testOpts,strip_tags($options->output()));
    }
}

?>
