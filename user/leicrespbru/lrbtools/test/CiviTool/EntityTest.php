<?php
set_include_path(get_include_path().":".getcwd()."/lib:".getcwd()."/test");

require_once("CiviTool/TestBase.php");
require_once("LRB/CiviTool/Entity.php");

class CiviTool_EntityTest extends CiviTool_TestBase {
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testEntityCreateFailsWhenNoType() {
		$option = array(
		);
		$as = LRB_CiviTool_Entity::factory($option);
	}
	function testEntityCreateWorksWithMixedCase() {
		$option = array(
			'Type' => 'ActivityStatus'
		);
		$as = LRB_CiviTool_Entity::factory($option);
		$this->assertEquals("LRB_CiviTool_Entity_ActivityStatus",get_class($as));
	}
	function testEntityCreateWorksWithLowerCaseAndUnderScore() {
		$option = array(
				'Type' => 'activity_status'
		);
		$as = LRB_CiviTool_Entity::factory($option);
		$this->assertEquals("LRB_CiviTool_Entity_ActivityStatus",get_class($as));
	}
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testEntityCreateFailsWithUnrecognisedType() {
		$option = array(
				'Type' => 'activity_file'
		);
		$as = LRB_CiviTool_Entity::factory($option);
	}
	
	function testEntityContainsOptions() {
		$options = array(
			'Type' => 'ActivityStatus',
			'Name' => 'Test status',
			'Value' => 3
		);
		$as = LRB_CiviTool_Entity::factory($options);
		$this->assertJsonStringEqualsJsonString(json_encode($as->getOptions()),json_encode($options));
		$this->assertEquals("Test status",$as->getOption("Name"));
		$this->assertEquals(3,$as->getOption("Value"));
	}
	
	function testEntityGetOptionReturnsDefaultIfNotExisting() {
		$options = array(
				'Type' => 'ActivityStatus',
				'Name' => 'Test status',
				'Value' => 3
		);
		$as = LRB_CiviTool_Entity::factory($options);
		$opt = $as->getOption("No exist","test");
		$this->assertEquals("test",$opt);
	}	
	
	function testEntityGetLabelAndNameDeterminesLabelIfOnlyName() {
		$options = array(
				'Type' => 'ActivityStatus',
				'Name' => 'test_status',
				'Value' => 3
		);
		$as = LRB_CiviTool_Entity::factory($options);
		list($label,$name) = $as->getLabelAndName();

		$this->assertEquals("test_status",$name);
		$this->assertEquals("Test status",$label);
	}

	function testEntityGetLabelAndNameDeterminesNameIfOnlyLabel() {
		$options = array(
				'Type' => 'ActivityStatus',
				'Label' => 'Testing status',
				'Value' => 3
		);
		$as = LRB_CiviTool_Entity::factory($options);
		list($label,$name) = $as->getLabelAndName();

		$this->assertEquals("testing_status",$name);
		$this->assertEquals("Testing status",$label);
	}
	
	/**
	 * @expectedException LRB_CiviToolException
	 */
	function testEntityGetLabelAndNameFailsIfNone() {
		$options = array(
				'Type' => 'ActivityStatus',
				'Value' => 3
		);
		$as = LRB_CiviTool_Entity::factory($options);
		list($label,$name) = $as->getLabelAndName();
	}
}

