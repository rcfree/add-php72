<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */
class ADD_Component_Test_Object extends ADD_Component {}
class ADD_ComponentTest extends ADD_BaseTestFixture {
    private $SUT;
    private $page;

    public function setUp() {
        $this->page = $this->getMockBuilder("ADD_Page")->disableOriginalConstructor()->getMock();
        $this->SUT = new ADD_Component_Test_Object($this->page);
    }

    /**
     * @test
     */
    public function it_should_return_the_correct_type_using_the_class_name() {
		$this->assertEquals("Test_Object",$this->SUT->getType());
	}
	
	/**
     * @test
     */
    public function it_should_return_a_setting_from_the_page_prefixed_by_the_component_type() {
		$this->page->expects($this->once())->method("getSetting")->with("Test_Object.testSetting")->will($this->returnValue(99));
		$this->assertEquals(99,$this->SUT->getSetting('testSetting'));
	}
	
	/**
     * @test
     */
    public function it_should_return_all_settings_from_the_page_prefixed_by_the_component_type() {
		$settings = array("Test_Object.testSetting1"=>"50","NonTest_Object.testSetting1"=>99,"testSetting2"=>"foo","Test_Object.testSetting2"=>"bar");
		$this->page->expects($this->once())->method("getSettings")->will($this->returnValue($settings));
		$this->assertEquals(array("testSetting1"=>50,"testSetting2"=>"bar"),$this->SUT->getAllSettings());
	}
}
 