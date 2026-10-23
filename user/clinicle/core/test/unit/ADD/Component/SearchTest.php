<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 21/04/2014
 * Time: 11:34
 */

class ADD_Component_SearchTest extends ADD_BaseTestFixture {

    private $page;
    private $SUT;
    private $encounterRecord;


    const PAGETITLE = "test123";

    public function setUp() {
        $this->initSystemAccess();

        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();

        $this->SUT = new ADD_Component_Search($this->page);
    }

    /**
     * @test
     */
    public function it_should_create_correct_expected_permission() {
        $expPerms = array();
        $this->SUT->expectedPermissions($expPerms);
        $this->assertEquals(array("search"),$expPerms);
    }

    /**
     * @test
     */
    public function it_should_stop_user_searching_if_no_search_permission() {
        $params = array(
            array("terms",null,""),
        );
        //$this->system->expects($this->once())->method("getParam")->will($this->returnValueMap($params));
        $this->setExpectedException("ADD_NoPermissionToAccessException","Sorry you do not have permission to search patients.");

        $this->SUT->displayAccess();
    }
    
    /**
     * @test
     */
    public function it_should_present_no_results_if_blank_search() {
        $params = array(
            array("terms",null,""),
        );
        $this->expectThatPageHasPermissionTo("search");
        //$this->system->expects($this->once())->method("getParam")->will($this->returnValueMap($params));
        //$this->setExpectedException("ADD_NoPermissionToAccessException","Sorry you do not have permission to search patients.");
        
        $this->SUT->displayAccess();
    }

    private function expectThatPageHasPermissionTo($perm)
    {
        $this->page->expects($this->once())
            ->method("getPermission")
            ->with($perm)
            ->will($this->returnValue(true));
    }
   
}
 