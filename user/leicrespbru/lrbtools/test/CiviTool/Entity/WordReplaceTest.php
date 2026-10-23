<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");

class CiviTool_Entity_WordReplaceTest extends CiviTool_TestBase {	
		function testWordReplaceCreation() {
			$option = array(
					'Type' => 'WordReplace',
					'Label' =>'Replace',
					'Name' => 'With This',
					'Style'=>'wildcard'
			);
			$entity = LRB_CiviTool_Entity::factory($option);
			$entity->create();
			$this->assertTrue($entity->exists());
			$entity->delete();
			$this->assertFalse($entity->exists());
		}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
		function testNonExistingWordReplaceDeletionFails() {
			$option = array(
					'Type' => 'WordReplace',
					'Label' =>'Case',
					'Name' => 'Not existing',
					'Style'=>'wildcard'
			);
			$entity = LRB_CiviTool_Entity::factory($option);
			$entity->delete();
		}
}

