<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");


class CiviTool_Entity_CustomFieldTest extends CiviTool_TestBase {

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfCustomFieldWithNoParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'my_non_parent_custom_field'));
		$entity->create();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testRepeatCreationOfCustomFieldFails() {
		$entity1 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_group', 'Parent'=>'Contact'));
		$entity2 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'my_custom_group_repeat', 'Parent'=>'my_custom_group'));
		$entity1->create();
		$entity2->create();
		$entity2->create();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testDeletionOfNonExistingCustomFieldFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'my_non_def_custom_group'));
		$entity->delete();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfCustomFieldWithNotExistingParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_undefined_custom_group', 'Parent'=>'Not existing'));
		$entity->create();
	}
	
	function testCreationOfSingleCustomFieldInGroup() {
		$entity1 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_group1', 'Label' => 'My custom contact group 1', 'Parent'=>'Contact'));
		$entity1->create();
		$entity2 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Label' => 'My custom field 1', 'Parent'=>'my_custom_group1'));
		$entity2->create();
		$cf = $entity2->get();
		$this->assertEquals('My_custom_field_1',$cf['name']);
		$cg = $entity1->get();
		$this->assertEquals($cf['custom_group_id'],$cg['id']);
		$this->assertTrue($entity2->exists());
		$entity2->delete();
		$this->assertFalse($entity2->exists());
		$entity1->delete();
	}

	function testCreationOfMultipleCustomFieldsInGroup() {
		$entity1 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'my_custom_group2', 'Label' => 'My custom contact group 2', 'Parent'=>'Contact'));
		$entity1->create();
		$entity2 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField', 'Label' => 'My custom field 2a', 'Parent'=>'my_custom_group2'));
		$entity2->create();
		$entity3 = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Label' => 'My custom field 2b', 'Parent'=>'my_custom_group2'));
		$entity3->create();
		$cf2 = $entity2->get();
		$cf3 = $entity3->get();
		$this->assertEquals($cf2['name'],'My_custom_field_2a');
		$this->assertEquals($cf3['name'],'My_custom_field_2b');
		$cg = $entity1->get();
		$this->assertEquals($cf2['custom_group_id'],$cg['id']);
		$this->assertEquals($cf3['custom_group_id'],$cg['id']);
		$this->assertTrue($entity2->exists());
		$this->assertTrue($entity3->exists());
		$entity2->delete();
		$entity3->delete();
		$this->assertFalse($entity2->exists());
		$this->assertFalse($entity3->exists());
		$entity1->delete();
	}

	function testDeletionofCustomFieldWorks() {
		$cg = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'custom_group_to_delete', 'Label' => 'My custom contact group to delete', 'Parent'=>'Contact'));
		$cg->create();
		$cf = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'custom_field_to_delete','Label'=>'Custom field to delete','Parent'=>'custom_group_to_delete'));
		$cf->create();
		$this->assertTrue($cf->exists());
		$cf->delete();
		$this->assertFalse($cf->exists());
		$cg->delete();
		$this->assertFalse($cg->exists());
	}

	function testNonExistingCustomFieldAndGroupDoesNotExist() {
		$this->entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'not_a_real_custom_field','Parent'=>'not_a_real_group'));
		$this->assertFalse($this->entity->exists());
	}
	
	function testNonExistingCustomFieldInExistingGroupDoesNotExist() {
		$cg = LRB_CiviTool_Entity::factory(array('Type'=>'CustomGroup','Name'=>'existing_group','Parent'=>'Contact'));
		$cg->create();
		
		$this->entity = LRB_CiviTool_Entity::factory(array('Type'=>'CustomField','Name'=>'not_a_real_custom_field','Parent'=>'existing_group'));
		$this->assertFalse($this->entity->exists());
		$cg->delete();
	}
}
?>
