<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/04/2014
 * Time: 10:28
 */

class ADD_PageTest extends ADD_BaseTestFixture {
    const TESTPAGE = "testpage";

    private $module;
    private $settings;
    private $SUT;

    const OTHERPAGE = "otherpage";

    public function setUp() {
        $this->module = $this->getMockBuilder("ADD_Module")->disableOriginalConstructor()->getMock();
        $this->settings = $this->getMockBuilder("ADD_Config")->disableOriginalConstructor()->getMock();
        $this->SUT = new ADD_Page(self::TESTPAGE,$this->settings,$this->module);
    }

    /**
     * @test
     */
    public function it_should_return_the_name() {
        $this->assertEquals(self::TESTPAGE,$this->SUT->getName());
    }

    /**
     * @test
     */
    public function it_should_set_a_different_name() {
        $this->SUT->setName(self::OTHERPAGE);
        $this->assertEquals(self::OTHERPAGE,$this->SUT->getName());
    }

}
 