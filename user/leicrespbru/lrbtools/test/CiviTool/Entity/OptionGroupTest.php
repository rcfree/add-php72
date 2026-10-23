<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");


class CiviTool_Entity_OptionGroupTest extends CiviTool_TestBase {
	

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testRepeatCreationOfOptionGroupFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'my_option_group_repeat'));
		$entity->create();
		$entity->create();
	}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testDeletionOfNonExistingCustomGroupFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'my_option_group'));
		$entity->delete();
	}
	
	function testCreationOfOptionGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'my_custom_option_group'));
		$entity->create();
		$cg = $entity->get();

		$this->assertEquals($cg['name'],'my_custom_option_group');
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}

	function testDeletionofOptionGroupWorks() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'option_group_to_delete','Label'=>'Option group to delete'));
		$entity->create();
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testFakeOptionGroupDoesNotExist() {
		$this->entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'not_a_real_option_group'));
		$this->assertFalse($this->entity->exists());
	}
	protected function tearDown() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'OptionGroup','Name'=>'my_option_group_repeat'));
		if ($entity->exists()) $entity->delete();
	}
}

