<?php
set_include_path(get_include_path().":".dirname(__FILE__)."/../../../../../lib");
require_once("ADD/Pathology/Record/Haem.php");

class ADD_Pathology_Record_HaemTest extends ADD_BaseTestFixture {
    private $SUT;
    const UNIQUEID="6325666242||T||PLT||20140402233410";
	const SNUMBER="S9999999";
    public function setUp() {
    	$this->SUT = new ADD_Pathology_Record_Haem();
    }

	/**
     * @test
	 * @expectedException ADD_NullValueProvidedException
     */
    public function setDataFromRow_fails_if_no_system_number() {
    	$this->SUT->setDataFromRow(array("UNIQUE_TEST_ID"=>self::UNIQUEID));
	}
	
    /**
     * @test
     */
    public function hasData_returns_true_if_required_data_is_present() {
    	$this->SUT->setDataFromRow(array("SYSTEM_NUMBER"=>self::SNUMBER,"UNIQUE_TEST_ID"=>self::UNIQUEID));
    	$this->assertTrue($this->SUT->hasData());
	}
	
	 /**
     * @test
     */
    public function hasData_returns_false_if_data_is_not_present() {
    	$this->assertFalse($this->SUT->hasData());
	}

	public function getInputData() {
		return array('UNIQUE_TEST_ID' => self::UNIQUEID,
    'SYSTEM_NUMBER'=>self::SNUMBER,
	  'SPECIMEN_NUMBER' => "CP225724W",
	  'SAMPLE_COLLECTION_DATE_TIME' => '2014-03-10 18:24:00.000',
	  'LOCAL_TEST_CODE' => 'PLT',
	  'SNOMED_CT_CODE' => '365632008',
	  'Result' => '285',
	  'REFERENCE_RANGE_LOW'=>140,
	  'REFERENCE_RANGE_HIGH'=>400,
	  'UNITS'=>'x10^9/L'
	  );
	}
	  public function getOutputData() {
		  return array (
	  'unique_test_id' => self::UNIQUEID,
	  'specimen_no' => "CP225724W",
	  'coll_date_time' => '10/03/2014 18:24',
	  'test_code' => 'PLT',
	  'snomed_ct_code' => '365632008',
	  'units' => 'x10^9/L',		
      'normal_low'=>140,
      'normal_high'=>400,
	  'result' => '285',
	);
	  }
	/**
     * @test
     */
    public function setDataFromRow_correctly_populates_object_with_input_data() {
    	$inputData = $this->getInputData();
    	$this->SUT->setDataFromRow($inputData);
		$this->assertEquals($this->getOutputData(),$this->SUT->getDataAsObsValues());
		$this->assertEquals(self::SNUMBER,$this->SUT->getPatientIdent());
		$this->assertEquals(self::UNIQUEID,$this->SUT->getUniqueId());
	}
	function expectsResultItem() {
		$this->resultItem = $this->getMockBuilder("ADD_Query_ResultItem")->disableOriginalConstructor()->getMock();
		$encData = array("path-tests-haem"=>array(self::RECORDID=> $this->getOutputData()));
		$this->resultItem->expects($this->once())->method("getEncounterData")->will($this->returnValue($encData));
		$this->resultItem->expects($this->once())->method("getPatientData")->will($this->returnValue(self::SNUMBER));
	}
	
	const RECORDID=989;
	
    /**
     * @test
     */
    public function setDataFromResultItem_correctly_populates_object_with_current_data() {
    	set_include_path(getenv('ADD_PATH')."/work/query/lib:".get_include_path());
		require_once("ADD/Query/Value/ObsData.php");	
    	require_once("ADD/Query/ResultItem.php");
		
		$outputData = $this->getOutputData();
		$this->expectsResultItem();
    	$this->SUT->setDataFromResultItem($this->resultItem);
		
		$this->assertEquals($outputData,$this->SUT->getDataAsObsValues());
		$this->assertEquals(self::SNUMBER,$this->SUT->getPatientIdent());
		$this->assertEquals(self::UNIQUEID,$this->SUT->getUniqueId());
		$this->assertEquals(self::RECORDID,$this->SUT->getRecordId());
	}

	/**
     * @test
     */
    public function hasChanged_returns_false_by_default() {
    	$this->assertFalse($this->SUT->hasChanged());
	}
	
	/**
     * @test
     */
    public function hasChanged_returns_false_when_setDataFromResultItem() {
    	$this->expectsResultItem();
    	$this->SUT->setDataFromResultItem($this->resultItem);
    	$this->assertFalse($this->SUT->hasChanged());
	}
	
	/**
     * @test
     */
    public function hasChanged_returns_true_when_other_data_changes() {
    	$this->expectsResultItem();
    	$this->SUT->setDataFromResultItem($this->resultItem);
		$inputData = $this->getInputData();
		$inputData['Result']="123";
    	$this->SUT->updateFromRowData($inputData);
    	$this->assertTrue($this->SUT->hasChanged());
    }

	/**
     * @test
     */
    public function hasChanged_returns_false_when_other_data_is_same() {
    	$this->expectsResultItem();
    	$this->SUT->setDataFromResultItem($this->resultItem);
		$inputData = $this->getInputData();
		print_r($inputData);
		print_r($this->SUT);
    	$this->SUT->updateFromRowData($inputData);
    	$this->assertFalse($this->SUT->hasChanged());
    }
}
 