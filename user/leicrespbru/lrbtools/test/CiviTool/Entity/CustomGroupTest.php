<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");

class CiviTool_Entity_CustomGroupTest extends CiviTool_TestBase {
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfCustomGroupWithNoParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_non_def_custom_group'));
		$entity->create();
	}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testRepeatCreationOfCustomGroupFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_group_repeat', 'Parent'=>'Contact'));
		$entity->create();
		$entity->create();
	}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testDeletionOfNonExistingCustomGroupFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_non_def_custom_group'));
		$entity->delete();
	}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfCustomGroupWithUndefinedParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_undefined_custom_group', 'Parent'=>'Not existing'));
		$entity->create();
	}
	
	function testCreationOfCustomContactGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_contact_group', 'Label' => 'My custom contact group', 'Parent'=>'Contact'));
		$entity->create();
		$cg = $entity->get();

		$this->assertEquals($cg['name'],'my_custom_contact_group');
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testCreationOfCustomContactGroupByLabel() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup', 'Label' => 'My custom contact group', 'Parent'=>'Contact'));
		$entity->create();
		$cg = $entity->get();

		$this->assertEquals('my_custom_contact_group',$cg['name']);
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testCreationOfCustomIndividualGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_individual_group', 'Label' => 'My custom individual group', 'Parent'=>'Individual'));
		$entity->create();
		$cg = $entity->get();
	
		$this->assertEquals($cg['name'],'my_custom_individual_group');
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testCreationOfMeetingActivityCustomGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_meeting_group', 'Label' => 'My custom meeting group', 'Parent'=>'Activity=Meeting'));
		if ($entity->exists()) $entity->delete();
		$entity->create();
	
		$cg = $entity->get();
		$this->assertEquals($cg['name'],'my_custom_meeting_group');
		$this->assertEquals($cg['extends'],'Activity');
		$this->assertEquals($cg['is_active'],1);
		$this->assertEquals($cg['extends_entity_column_value'],array(1));
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testCreationOfMultipleActivityCustomGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_multiple_custom_meeting_group', 'Label' => 'My multiple custom meeting group', 'Parent'=>'Activity=Meeting,Print PDF Letter'));
		if ($entity->exists()) $entity->delete();
		$entity->create();
	
		$cg = $entity->get();
		$this->assertEquals($cg['name'],'my_multiple_custom_meeting_group');
		$this->assertEquals($cg['extends'],'Activity');
		$this->assertEquals($cg['is_active'],1);
		$this->assertEquals($cg['extends_entity_column_value'],array(1,22));
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testCreationOfCaseCustomGroup() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'cust_case_group', 'Parent'=>'Case'));
		if ($entity->exists()) $entity->delete();
		$entity->create();
	
		$cg = $entity->get();
		$this->assertEquals($cg['name'],'cust_case_group');
		$this->assertEquals($cg['extends'],'Case');
		$this->assertEquals($cg['is_active'],1);
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testDeletionofCustomGroupWorks() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'custom_group_to_delete','Label'=>'Custom group to delete','Parent'=>'Contact'));
		$entity->create();
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	
	function testCreationOfMultipleCaseCustomGroup() {
		$ct1 = LRB_CiviTool_Entity::factory(array('Type'=>'CaseType','Name'=>'test_case_type1'));
		if (!$ct1->exists()) $ct1->create();
		$ct2 = LRB_CiviTool_Entity::factory(array('Type'=>'CaseType','Name'=>'test_case_type2'));
		if (!$ct2->exists()) $ct2->create();
		
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'custom_case', 'Label' => 'Custom group','Parent'=>'Case=test_case_type1,test_case_type2'));

		$entity->create();
		$ct1vals = $ct1->get();
		$ct2vals = $ct2->get();
		$cg = $entity->get();
		$this->assertEquals($cg['name'],'custom_case');
		$this->assertEquals($cg['extends'],'Case');
		$this->assertEquals($cg['is_active'],1);
		$this->assertEquals($cg['extends_entity_column_value'],array($ct1vals['value'],$ct2vals['value']));
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
		$ct1->delete();
		$ct2->delete();
	}
	
	function testFakeCustomGroupDoesNotExist() {
		$this->entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'not_a_real_custom_group'));
		$this->assertFalse($this->entity->exists());
	}

}

