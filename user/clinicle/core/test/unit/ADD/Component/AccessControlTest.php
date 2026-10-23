<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 21/04/2014
 * Time: 11:34
 */

class ADD_Component_AccessControlTest extends ADD_BaseTestFixture {

    private $page;
    private $SUT;
    private $encounterRecord;


    const PAGETITLE = "test123";

    public function setUp() {
        $this->initSystemAccess();

        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
		$this->encounterRecord = $this->getMock("ADD_EncounterRecord");
        $this->SUT = new ADD_Component_AccessControl($this->page);
    }

    /**
     * @test
     */
    public function it_should_create_correct_expected_permissions_when_page_setting_supplied() {
        $expPerms = array();
        $this->expectThatPageSettingIs("AccessControl.permissions","create edit view");
        $this->SUT->expectedPermissions($expPerms);
        $this->assertEquals(array("create","edit","view"),$expPerms);
    }

    /**
     * @test
     */
    public function it_should_create_no_permissions_when_page_setting_not_supplied() {
        $expPerms = array();
        $this->expectThatPageSettingIs("AccessControl.permissions",null);

        $this->SUT->expectedPermissions($expPerms);
        $this->assertEquals(array(),$expPerms);
    }

    /**
     * @test
     */
    public function it_should_stop_user_creating_record_if_record_does_not_exist_and_no_create_permission() {
		$this->expectThatPageDoesHaveComponent("Encounter");
		
        $this->expectThatPageHasEncounterRecord();

        $this->expectThatSystemReturnsParam("eid",null);
        $this->expectThatEncounterRecordDoesNotExist();

        $this->setExpectedException("ADD_NoPermissionToAccessException","You do not have permission to create this record.");

        $this->SUT->displayAccess();
    }

    /**
     * @test
     */
    public function it_should_allow_user_to_create_record_if_record_does_not_exist_and_create_permission() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatSystemReturnsParam("eid",null);
        $this->expectThatEncounterRecordDoesNotExist();

        $this->expectThatPageHasPermissionTo("create");

        $this->SUT->displayAccess();

    }

    /**
     * @test
     */
    public function it_should_be_readonly_if_record_does_not_exist_and_user_has_no_create_permission_but_auto_create_is_set() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesNotExist();

        $this->expectThatPageSettingIs("auto-create",1);
        $this->expectThatSystemStateIsSetTo("readOnly",true);
        $this->expectThatPageHasWarningAdded("You only have permission to view this record. You will not be able to make changes.");

        $this->SUT->displayAccess();
    }

    /**
     * @test
     */
    public function it_should_prevent_user_viewing_if_no_view_permission_and_view_is_only_access() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();

        $this->expectThatPageHasExpectedPermissions(array("view"));

        $this->setExpectedException("ADD_NoPermissionToAccessException","You do not have permission to view this record.");

        $this->SUT->displayAccess();
    }

    /**
     * @test
     */
    public function it_should_allow_user_viewing_if_view_permission_and_view_is_only_access() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();
        $this->expectThatPageHasPermissionTo("view");
        $this->expectThatSystemStateIsNotSet();
        $this->expectThatPageHasNoWarningsAdded();
        $this->expectThatPageHasExpectedPermissions(array("view"));

        $this->SUT->displayAccess();
    }
    
    /**
     * @test
     */
    public function it_should_allow_user_to_view_if_record_exists_and_edit_permission() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();
        $this->expectThatPageHasPermissionTo("edit");
        $this->expectThatSystemStateIsNotSet();
        $this->expectThatPageHasNoWarningsAdded();
        $this->expectThatPageHasExpectedPermissions(array("create","edit","view"));

        $this->SUT->displayAccess();
    }

    /**
     * @test
     */
    public function it_should_allow_user_if_non_encounter_page_and_user_has_view_permission() {
        $this->expectThatPageDoesNotHaveComponent("Encounter");
		
		$this->expectThatPageHasNoWarningsAdded();
        
        $this->page->expects($this->exactly(1))->method("getPermission")->will($this->returnValueMap(array(array("view",true))));
        
        $this->SUT->displayAccess();

    }
	
	/**
     * @test
     */
    public function it_should_prevent_user_if_non_encounter_page_and_user_has_no_view_permission() {
        $this->expectThatPageDoesNotHaveComponent("Encounter");
		
		$this->expectThatPageHasNoWarningsAdded();
        
        $this->page->expects($this->exactly(1))->method("getPermission")->will($this->returnValueMap(array(array("view",false))));
        
		$this->setExpectedException("ADD_NoPermissionToAccessException","You do not have permission to view this page.");

        $this->SUT->displayAccess();
		
    }

    /**
     * @test
     */
    public function it_should_prevent_access_if_record_exists_and_user_has_no_view_and_edit_permission() {
        $this->expectThatPageDoesHaveComponent("Encounter");
		
		$this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();

        $this->expectThatPageHasExpectedPermissions(array("view","edit"));

        $this->setExpectedException("ADD_NoPermissionToAccessException","You do not have permission to view or edit this record.");

        $this->SUT->displayAccess();

    }

    /**
     * @test
     */
    public function it_should_prevent_submission_if_user_has_no_permissions_and_page_is_not_encounter_record_based() {
        $this->expectThatPageDoesNotHaveComponent("Encounter");
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasAnyPermissions(array("create","edit","view"),false);
        $this->setExpectedException("ADD_NoPermissionToSubmitException","Sorry you are not allowed to change '".self::PAGETITLE."' data");

        $this->SUT->submitAccess(array());

    }

    /**
     * @test
     */
    public function it_should_allow_submission_if_user_has_any_permissions_and_page_is_not_encounter_record_based() {
        $this->expectThatPageDoesNotHaveComponent("Encounter");
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasAnyPermissions(array("create","edit","view"),true);

        $this->SUT->submitAccess(array());

    }

    /**
     * @test
     */
    public function it_should_prevent_submission_if_no_create_permission_and_no_encounter_record() {
        $this->expectThatPageDoesHaveComponent("Encounter");
        $this->expectThatPageHasEncounterRecord();
        $this->expectThatPageHasPermissionsTo(array("create","edit","view"),array(false,true,true));
        $this->expectThatEncounterRecordDoesNotExist();
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->setExpectedException("ADD_NoPermissionToSubmitException","Sorry you do not have permission to create '".self::PAGETITLE."' data");

        $this->SUT->submitAccess(array());
    }

    /**
     * @test
     */
    public function it_should_allow_submission_if_create_permission_and_no_encounter_record() {
        $this->expectThatPageDoesHaveComponent("Encounter");
        $this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesNotExist();
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasPermissionsTo(array("create","edit","view"),array(true,true,true));

        $this->expectThatPageHasNoWarningsAdded();
        $this->expectThatSystemStateIsNotSet();

        $this->SUT->submitAccess(array());
    }

    /**
     * @test
     */
    public function it_should_allow_submission_if_edit_permission_and_existing_encounter_record() {
        $this->expectThatPageDoesHaveComponent("Encounter");
        $this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasPermissionsTo(array("create","edit","view"),array(true,true,true));

        $this->expectThatPageHasNoWarningsAdded();
        $this->expectThatSystemStateIsNotSet();

        $this->SUT->submitAccess(array());
    }

    /**
     * @test
     */
    public function it_should_prevent_submission_of_existing_encounter_record_if_only_view_permission() {
        $this->expectThatPageDoesHaveComponent("Encounter");
        $this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasPermissionsTo(array("create","edit","view"),array(false,false,true));

        $this->setExpectedException("ADD_NoPermissionToSubmitException","Sorry you do not have permission to edit '".self::PAGETITLE."' data");

        $this->SUT->submitAccess(array());
    }

    /**
     * @test
     */
    public function it_should_prevent_submission_of_existing_encounter_record_if_no_edit_or_view_permission() {
        $this->expectThatPageDoesHaveComponent("Encounter");
        $this->expectThatPageHasEncounterRecord();
        $this->expectThatEncounterRecordDoesExist();
        $this->expectThatPageSettingIs("title", self::PAGETITLE);
        $this->expectThatPageHasPermissionsTo(array("create","edit","view"),array(true,false,false));

        $this->setExpectedException("ADD_NoPermissionToSubmitException","Sorry you do not have permission to edit '".self::PAGETITLE."' data");

        $this->SUT->submitAccess(array());
    }

    private function expectThatEncounterRecordDoesNotExist()
    {
        $this->encounterRecord
            ->expects($this->once())
            ->method("exists")
            ->will($this->returnValue(false));
    }

    private function expectThatEncounterRecordDoesExist()
    {
        $this->encounterRecord
            ->expects($this->once())
            ->method("exists")
            ->will($this->returnValue(true));
    }

    private function expectThatPageHasEncounterRecord()
    {
        $this->page
            ->expects($this->once())
            ->method("getEncounterRecord")
            ->will($this->returnValue($this->encounterRecord));
    }

    private function expectThatSystemReturnsParam($param,$value)
    {
        $this->system
            ->expects($this->once())
            ->method("getParam")
            ->with($param)
            ->will($this->returnValue($value));
    }

    private function expectThatPageHasPermissionTo($perm)
    {
        $this->page->expects($this->once())
            ->method("getPermission")
            ->with($perm)
            ->will($this->returnValue(true));
    }

    private function expectThatPageHasPermissionsTo($perms,$access)
    {
        $this->page->expects($this->once())
            ->method("getPermissions")
            ->with($perms)
            ->will($this->returnValue($access));
    }

    private function expectThatPageSettingIs($setting,$value)
    {
        $this->page->expects($this->once())
            ->method("getSetting")
            ->with($setting)
            ->will($this->returnValue($value));
    }

    private function expectThatSystemStateIsSetTo($state,$value)
    {
        $this->system->expects($this->once())
            ->method("setState")
            ->with($state,$value);
    }

    private function expectThatSystemStateIsNotSet()
    {
        $this->system->expects($this->never())
            ->method("setState");
    }

    private function expectThatPageHasWarningAdded($warning)
    {
        $this->page->expects($this->once())
            ->method("addWarning")
            ->with($warning);
    }

    private function expectThatPageHasNoWarningsAdded()
    {
        $this->page->expects($this->never())
            ->method("addWarning");
    }

    private function expectThatPageHasExpectedPermissions($expPerms)
    {
        $this->page->expects($this->once())
            ->method("getExpectedPermissions")
            ->will($this->returnValue($expPerms));
    }


    private function expectThatPageHasAnyPermissions($perms,$returned)
    {
        $this->page->expects($this->once())->method("hasAnyPermissions")
            ->with($perms)
            ->will($this->returnValue($returned));
    }

    private function expectThatPageDoesNotHaveComponent($name)
    {
        $this->page->expects($this->once())
            ->method("hasComponent")
            ->with($name)
            ->will($this->returnValue(false));
    }
    private function expectThatPageDoesHaveComponent($name)
    {
        $this->page->expects($this->once())
            ->method("hasComponent")
            ->with($name)
            ->will($this->returnValue(true));
    }
}
 