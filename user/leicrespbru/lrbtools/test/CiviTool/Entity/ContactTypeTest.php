<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");


class CiviTool_Entity_ContactTypeTest extends CiviTool_TestBase {

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfContactTypeWithNoParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'my_non_parent_contact_type'));
		$entity->create();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testRepeatCreationOfContactTypeFails() {
		$entity2 = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'my_contact_type', 'Parent'=>'individual'));
		$entity2->create();
		$entity2->create();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testDeletionOfNonExistingContactTypeFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'my_non_def_contact_type'));
		$entity->delete();
	}

	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testCreationOfContactTypeWithNotExistingParentFails() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'my_undefined_contact_type', 'Parent'=>'Not existing'));
		$entity->create();
	}
	
	function testNonExistingContactTypeDoesNotExist() {
		$this->entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'not_a_real_custom_field','Parent'=>'not_a_real_group'));
		$this->assertFalse($this->entity->exists());
	}
	
	
	function testCreationOfContactType() {
		$parent = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'Individual'));
		$parentId = $parent->getId();	
		
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Label' => 'Contact test', 'Parent'=>'Individual'));
		$entity->create();
		$ct = $entity->get();
		$this->assertEquals('contact_test',$ct['name']);
		$this->assertEquals($ct['parent_id'],$parentId);
		$this->assertTrue($entity->exists());
		$entity->delete();
		$this->assertFalse($entity->exists());
	}
	
	function testGetExistingContactTypes() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Label' => 'Individual'));
		$ct = $entity->get();
		$this->assertEquals('Individual',$ct['name']);
		
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Label' => 'Organization'));
		$ct = $entity->get();
		$this->assertEquals('Organization',$ct['name']);
	}

	function testGetNonExistingContactType() {
		$entity = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Label' => 'Not real'));
		$ct = $entity->get();
		$this->assertEquals(null,$ct);
	}

	function testDeletionofContactTypeWorks() {
		$ct = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'custom_ct_to_delete','Parent'=>'Organization'));
		$ct->create();
		$this->assertTrue($ct->exists());
		$ct->delete();
		$this->assertFalse($ct->exists());
	}
	static function tearDownAfterClass() {
		$entity2 = LRB_CiviTool_Entity::factory(array('Type'=>'ContactType','Name'=>'my_contact_type', 'Parent'=>'individual'));
		$entity2->delete();
	}
}
?>
