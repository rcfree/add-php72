<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_WebTest extends ADD_BaseTestFixture {
    private $SUT;
    private $page;
    
    public function setUp() {
        $this->SUT = new ADD_Web("Html");
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function it_should_create_the_content_type_using_components_in_the_page() {
        $components = $this->getMockBuilder("ADD_ComponentCollection")->disableOriginalConstructor()->getMock();
        $this->page->expects($this->once())->method("getComponents")->will($this->returnValue($components));
        $this->assertEquals("Html",$this->SUT->createContentType($this->page));
    }

   

}
 