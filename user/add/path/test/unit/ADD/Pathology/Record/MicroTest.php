<?php

class ADD_Pathology_Record_MicroTest extends PHPUnit_Framework_TestCase {
    private $SUT;
    const UNIQUEID="6347538161||I||TBREF1||20141202094530";
	const SNUMBER="S9999999";
	
	const ORGANISM1="Mycobacterium tuberculosis";
	const ORGANISM2="Mycobacterium chelonae";
	const QUANT1="ISO"	;
	const QUANT2="TFO";
	
    public function setUp() {
    	$this->SUT = new ADD_Pathology_Record_Micro();
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

    	
	/**
     * @test
     */
    public function updateFromRowData_updates_appropriate_sensitivity() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"R"));
		$this->assertEquals(array("organisms"=>
			array(
				self::ORGANISM1."||".self::QUANT1=>array(
					"sens"=>array("ETH"=>"R"),"organism"=>self::ORGANISM1,"quantifier"=>self::QUANT1
				)
			)
		),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
	 * @expectedException ADD_Pathology_DuplicateSensException
     */
    public function addSens_fails_on_different_sensitivity() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"R"));
		$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"S"));
		
		$this->assertEquals(array("organisms"=>
			array(
				self::ORGANISM1."||".self::QUANT1=>array(
					"sens"=>array("ETH"=>"R"),"organism"=>self::ORGANISM1,"quantifier"=>self::QUANT1
				)
			)
		),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
     */
    public function addSens_ignores_duplicate_sensitivity() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"R"));
		$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"R"));
		$this->assertEquals(array("organisms"=>
			array(
				self::ORGANISM1."||".self::QUANT1=>array(
					"sens"=>array("ETH"=>"R"),"organism"=>self::ORGANISM1,"quantifier"=>self::QUANT1
				)
			)
		),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
     */
    public function addSens_with_suppressed_result_ignores_different_sensitivity() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"R","SUPPRESSION_FLAG_INTERPRETED"=>"N"));
		$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"ETH","INTERPRETED_SENSITIVITY_RESULT"=>"S","SUPPRESSION_FLAG_INTERPRETED"=>"Y"));
		$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"RIF","INTERPRETED_SENSITIVITY_RESULT"=>"S","SUPPRESSION_FLAG_INTERPRETED"=>"N"));
		$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1,"QUANTIFIER"=>self::QUANT1,"ANTIBIOTIC_CODE"=>"AMP","INTERPRETED_SENSITIVITY_RESULT"=>"S","SUPPRESSION_FLAG_INTERPRETED"=>""));
		
		$this->assertEquals(array("organisms"=>
			array(
				self::ORGANISM1."||".self::QUANT1=>array(
					"sens"=>array("ETH"=>"R","RIF"=>"S","AMP"=>"S"),"organism"=>self::ORGANISM1,"quantifier"=>self::QUANT1
				)
			)
		),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
     */
    public function addOrganism_adds_organisms_to_list() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1));
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM2));
		$this->assertEquals(array("organisms"=>array(self::ORGANISM1."||"=>array("sens"=>array(),"organism"=>self::ORGANISM1),self::ORGANISM2."||"=>array("sens"=>array(),"organism"=>self::ORGANISM2))),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
     */
    public function addOrganism_ignores_duplicate_organism() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1));
    	$this->SUT->updateFromRowData(array("ORGANISM"=>self::ORGANISM1));
    	$this->assertEquals(array("organisms"=>array(self::ORGANISM1."||"=>array("sens"=>array(),"organism"=>self::ORGANISM1))),$this->SUT->getDataAsObsValues());

	}
	
	/**
     * @test
     */
    public function addOrganism_ignores_null_organism() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>null));
    	$this->assertEquals(array(),$this->SUT->getDataAsObsValues());
	}
	
	/**
     * @test
     */
    public function addOrganism_ignores_empty_organism() {
    	$this->SUT->updateFromRowData(array("ORGANISM"=>""));
    	$this->assertEquals(array(),$this->SUT->getDataAsObsValues());
	}
	
	public function getInputData() {
		return array('UNIQUE_TEST_ID' => self::UNIQUEID,
    'SYSTEM_NUMBER'=>self::SNUMBER,
	  'SPECIMEN_NUMBER' => "MG706496A",
	  'SAMPLE_COLLECTION_DATE_TIME' => '2014-10-15 10:36:00.000',
	  'LOCAL_TEST_CODE' => 'TBREF1',
	  'SNOMED_CT_CODE' => '',
	  'SPECIMEN_TYPE_CODE' => 'PUSSWA',
	  'SPECIMEN_TYPE'=>'Pus swab',
	  'RESULT_CODE'=>'R',
	  'RESULT' => 'r',
	  'ORGANISM_CODE'=>'B',
	  'ORGANISM'=>'',
	  'ORGANISM_ATTRIBUTE'=>null,
	  'ANTIBIOTIC_CODE'=>'INH',
	  'NUMERIC_SENSITIVITY_RESULT'=>null,
	  'INTERPRETED_SENSITIVITY_RESULT'=>'S',
	  'METHOD'=>'MANM');
	}
	  public function getOutputData() {
		  return array (
	  'unique_test_id' => self::UNIQUEID,
	  'specimen_no' => "MG706496A",
	  'coll_date_time' => '15/10/2014 10:36',
	  'test_code' => 'TBREF1',
	  'specimen_type_code' => 'PUSSWA',
	  'specimen_type' => 'Pus swab',
	  'result_code' => 'R',
	  'result' => 'r',
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
		$recordData = $this->getOutputData();
		$recordData['organisms']='{"0":"Mycobacterium tuberculosis","1":"Mycobacterium chelonae"}';
		$recordData['antibiotic_sens']='{"ETH":"R","INH":"S"}';
		$encData = array('path-tests-micro'=>array(self::RECORDID=> $recordData));
		$this->resultItem->expects($this->once())->method("getEncounterData")->will($this->returnValue($encData));
		$this->resultItem->expects($this->once())->method("getPatientData")->will($this->returnValue(self::SNUMBER));
	}
	
	const RECORDID=989;
	
    /**
     * @test
     */
    public function setDataFromResultItem_correctly_populates_object_with_current_data() {
    	$outputData = array (
		  'unique_test_id' => self::UNIQUEID,
		  'specimen_no' => "MG706496A",
		  'coll_date_time' => '15/10/2014 10:36',
		  'test_code' => 'TBREF1',
		  'specimen_type_code' => 'PUSSWA',
		  'specimen_type' => 'Pus swab',
		  'result_code' => 'R',
		  'result' => 'r',
		  'organisms'=>array("Mycobacterium tuberculosis","Mycobacterium chelonae"),
		);
		$this->expectsResultItem();
    	$this->SUT->setDataFromResultItem($this->resultItem);
		
		$this->assertEquals($outputData,$this->SUT->getDataAsObsValues());
		$this->assertEquals(self::SNUMBER,$this->SUT->getPatientIdent());
		$this->assertEquals(self::UNIQUEID,$this->SUT->getUniqueId());
		$this->assertEquals(self::RECORDID,$this->SUT->getRecordId());
	}
}
 