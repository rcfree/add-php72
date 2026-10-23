<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");

class CiviTool_Entity_ActivityStatusTest extends CiviTool_TestBase {
	
	protected function setUp()
	{
		$option = array(
			'Type' => 'ActivityStatus',
			'Name' =>'my_activity_status'
		);
		$this->entity = LRB_CiviTool_Entity::factory($option);
	}
	
	function testScheduledActivityStatusExists() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ActivityStatus','Name'=>'scheduled'));
		$this->assertTrue($entity->exists());
	}
	
	/**
	 * @expectedException LRB_LRB_CiviToolException
	 */
	function testDuplicateScheduledActivityStatusCannotBeAdded() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ActivityStatus','Name'=>'scheduled'));
		$entity->create();
	}
	
	function testFakeActivityStatusDoesNotExist() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ActivityStatus','Name'=>'does_not_exist'));
		$this->assertFalse($entity->exists());
	}
	
	function testActivityStatusIsDeleted() {
		$this->entity->create();
		$this->assertTrue($this->entity->exists());
		$this->entity->delete();
		$this->assertFalse($this->entity->exists());
	}
	
	function testActivityStatusCreatedSuccessfully() {
		$this->entity->create();
		
		$at = $this->entity->get();
		$this->assertArrayHasKey("name",$at);
		$this->assertEquals($at['name'],"my_activity_status");
		$this->assertEquals($at['is_active'],1);
		$this->entity->delete();
		$this->assertEquals($this->entity->get(),null);
	}
	
}

