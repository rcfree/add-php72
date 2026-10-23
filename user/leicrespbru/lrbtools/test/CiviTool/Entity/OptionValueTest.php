<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");


class CiviTool_Entity_OptionValueTest extends CiviTool_TestBase {
	private $og = null;
	
	protected function setUp() {
		$og = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'my_option_group'));
		$this->og = $og;
		$og->create();
	}
	
	function testDeletionOfOptionValue() {
		$ov = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Name'=>'my_opt_value1','Parent'=>'my_option_group'));
		$ov->create();
		$this->assertTrue($ov->exists());
		$ov->delete();
		$this->assertFalse($ov->exists());
	}
	
	function testCreationOfMultipleOptionValues() {
		$ov1 = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'1','Name'=>'my_opt_value1','Parent'=>'my_option_group'));
		$ov1->create();
		$this->assertTrue($ov1->exists());
		$ov2 = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'2','Name'=>'my_opt_value2','Parent'=>'my_option_group'));
		$ov2->create();
		$this->assertTrue($ov2->exists());
	}
	
	function testGetOptionValues() {
		$ov = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'1','Name'=>'my_opt_value1','Parent'=>'my_option_group'));
		$ov->create();
		
		$ov = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'1','Name'=>'my_opt_value1','Parent'=>'my_option_group'));
		$ovValues = $ov->get();
		$this->assertEquals("my_opt_value1",$ovValues['name']);
		
		$ov2 = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'2','Label'=>'My opt value 2','Parent'=>'my_option_group'));
		$ov2->create();
		
		$ov2 = LRB_CiviTool_Entity::factory(array('Type'=>'OptionValue','Value'=>'2','Label'=>'My opt value 2','Parent'=>'my_option_group'));
		$ov2Values = $ov2->get();
		$this->assertEquals("my_opt_value_2",$ov2Values['name']);
	}
	
	protected function tearDown() {
		$this->og->delete();
	}
}

