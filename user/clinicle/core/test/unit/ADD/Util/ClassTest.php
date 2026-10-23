<?php
require_once("ADD/UtilBaseTest.php");
class ADD_Util_ClassTest extends ADD_UtilBaseTest
{
    /**
     * @test
     */
	function it_should_load_a_class_file_with_single_path() {
		$mod = ADD_Util::loadClass(self::CLASSFILE1);
		$this->assertEquals(self::CLASSFILE1,get_class($mod));
        $this->assertTrue(class_exists(self::CLASSFILE1));
	}

    /**
     * @test
     */
    function it_should_load_a_class_file_with_nested_path() {
        $mod = ADD_Util::loadClass(self::CLASSFILE2);
        $this->assertEquals(self::CLASSFILE2,get_class($mod));
        $this->assertTrue(class_exists(self::CLASSFILE2));
    }
}

?>