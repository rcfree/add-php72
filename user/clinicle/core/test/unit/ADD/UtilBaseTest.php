<?php
date_default_timezone_set("Europe/London");

abstract class ADD_UtilBaseTest extends PHPUnit_Framework_TestCase
{
    const CLASSFILE1 = "ADD_Config";
    const CLASSFILE2 = "ADD_Content_Html";
    protected $initialiser;
    protected $system;

    function setUp() {
        $this->initialiser = $this->getMockBuilder("ADD_InitialiserInterface")->disableOriginalConstructor()->getMock();
        $this->system = $this->getMockBuilder("ADD_System")->disableOriginalConstructor()->getMock();
        $this->initialiser->expects($this->once())->method("getSystem")->will($this->returnValue($this->system));
        ADD::init($this->initialiser);
		ADD::sys()->setState(ADD_System::EVAL_EXCEPTION,true);
    }
   
}

?>