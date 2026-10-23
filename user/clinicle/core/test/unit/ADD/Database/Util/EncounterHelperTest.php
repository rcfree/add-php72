
<?php
date_default_timezone_set("Europe/London");

class ADD_EncounterHelperTest extends PHPUnit_Framework_TestCase
{
	public $SUT;
	const ENCTYPE="form1";
	const ENCID=1;
	public static $OBSFIELDS=array(
		"field1"=>array("id"=>1,"name"=>"field1","is_deleted"=>0),
		"1"=>array("id"=>1,"name"=>"field1","is_deleted"=>0)
	);
	public function setUp() {
		$this->SUT=new ADD_Database_Util_EncounterHelper();
		
	}
	
	/**
	 * @test
	 */
    public function observations_should_be_created_if_an_observation_does_not_exist_in_encounter() {
		$obs=array();
		$newValues=array("field1"=>"test");
		$expCreated = array(array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>"test"),null));
		$expUpdated=array();
		$this->assertExpectedObservations($obs, $newValues, $expCreated, $expUpdated);
	}
	
	/**
	 * @test
	 */
    public function observations_should_be_updated_if_they_already_exist() {
		$obs=array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>"test"));
		$newValues=array("field1"=>"test2");
		$expCreated=array();
		$expUpdated = array(array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>"test2"),null));
		
		$this->assertExpectedObservations($obs, $newValues, $expCreated, $expUpdated);
	}
	
	/**
	 * @test
	 */
    public function observations_should_be_created_if_the_value_is_zero() {
		$obs=array();
		$newValues=array("field1"=>0);
		$expCreated = array(array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>0),null));
		$expUpdated=array();
		
		$this->assertExpectedObservations($obs, $newValues, $expCreated, $expUpdated);
	}
	
	/**
	 * @test
	 */
    public function observations_should_be_updated_if_the_value_is_now_zero() {
		$obs=array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>2));
		$newValues=array("field1"=>0);
		$expCreated = array();
		$expUpdated=array(array(array("encounter_id"=>1,"observation_field_id"=>1,"is_serialized"=>0,"value"=>0),null));
		
		$this->assertExpectedObservations($obs, $newValues, $expCreated, $expUpdated);
	}
	
	
	
	public function assertExpectedObservations($obs, $newValues, $expCreated,$expUpdated) {
		list($createdObs,$updatedObs) = $this->SUT->determineObservations(self::ENCID, self::ENCTYPE,self::$OBSFIELDS, $obs, $newValues);
		
		$this->assertEquals($expCreated,$createdObs);
		$this->assertEquals($expUpdated,$updatedObs);
	}

	/**
	 * @test
	 */
	public function is_true_with_deletable_observations_returned_if_there_are_duplicate_fields_and_values() {
		$edb = $this->getMock("ADD_Database_Encounter");
		
		$obs = array(
			array(
				"id"=>1,
				"observation_field_id"=>"1",
				"value"=>"50",
				"encounter_id"=>1
			),
			array(
				"id"=>2,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
			array(
				"id"=>3,
				"observation_field_id"=>"1",
				"value"=>"50"
			),
			array(
				"id"=>4,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
		);
		list($canBeResolved,$duplicateObs) = $this->SUT->canResolveDuplicateObs($obs);
		$this->assertTrue($canBeResolved);
		$this->assertEquals(array(
			array(
				"id"=>3,
				"observation_field_id"=>"1",
				"value"=>"50"
			),array(
				"id"=>4,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
		),$duplicateObs);
	}
	
	/**
	 * @test
	 */
	public function is_false_with_no_observations_returned_if_they_are_duplicate_field_but_not_value() {
		$edb = $this->getMock("ADD_Database_Encounter");
		
		$obs = array(
			array(
				"id"=>1,
				"observation_field_id"=>"1",
				"value"=>"50",
				"encounter_id"=>1
			),
			array(
				"id"=>2,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
			array(
				"id"=>3,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
			array(
				"id"=>4,
				"observation_field_id"=>"1",
				"value"=>"70"
			)
		);
		list($canBeResolved,$duplicateObs) = $this->SUT->canResolveDuplicateObs($obs);
		$this->assertFalse($canBeResolved);
		$this->assertEquals(array(),$duplicateObs);
	}
	
	/**
	 * @test
	 */
	public function is_true_with_no_observations_returned_if_there_are_no_duplicates() {
		$edb = $this->getMock("ADD_Database_Encounter");
		
		$obs = array(
			array(
				"id"=>1,
				"observation_field_id"=>"1",
				"value"=>"50",
				"encounter_id"=>1
			),
			array(
				"id"=>2,
				"observation_field_id"=>"2",
				"value"=>"Y"
			),
			array(
				"id"=>3,
				"observation_field_id"=>"3",
				"value"=>"70"
			),
			array(
				"id"=>4,
				"observation_field_id"=>"4",
				"value"=>"Y"
			),
		);
		list($canBeResolved,$duplicateObs) = $this->SUT->canResolveDuplicateObs($obs);
		$this->assertTrue($canBeResolved);
		$this->assertEquals(array(),$duplicateObs);
	}
}

?>