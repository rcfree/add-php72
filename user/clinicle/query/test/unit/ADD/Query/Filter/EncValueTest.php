<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
;
;
class ADD_Query_Filter_EncValueTest extends ADD_Test_Query_Filter_BaseTest {
    function setUp() {
		parent::setUp();
		$this->resultItem = $this->getMockBuilder("ADD_Query_ResultItem")->disableOriginalConstructor()->getMock();
	}
	/**
     * @test
     */
    function filter_will_fail_if_field_is_not_allowed() {
        $this->setExpectedException("ADD_FieldDoesNotExistException");
		$this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"not_allowed","value"=>"1"));
        $this->SUT->filterEncounters($this->resultItem,$this->results);
    }
	
    /**
     * @test
     */
    function equal_op_will_include_patient_which_is_verified() {
        $this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"is_verified","value"=>"1"));
        $this->results->expects($this->once())->method("updateMatchingPatients");
		$this->resultItem->expects($this->never())->method("updateMatchingEncounters");
		
		$this->SUT->filterPatients($this->results);
    }
	
	/**
     * @test
     */
    function will_not_filter_patients_if_filter_data_is_true() {
        ADD::db('encounter')->expects($this->never())->method("fetchAll");
        $this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"is_verified","value"=>"1","type"=>ADD_Query_Filter::ENCOUNTER));
        $this->results->expects($this->never())->method("setPatientIds")->with(array(1,2));
		$this->resultItem->expects($this->once())->method("updateMatchingEncounters");
		$this->results->expects($this->never())->method("updateMatchingPatients");
        $this->SUT->filterEncounters($this->resultItem,$this->results);
    }
	
	/**
     * @test
     */
    function will_not_filter_data_if_filter_data_is_false() {
        $this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"is_verified","value"=>"1","type"=>ADD_Query_Filter::PATIENT));
        $this->results->expects($this->never())->method("setPatientIds")->with(array(1,2));
		$data = array();
        $this->SUT->filterEncounters($this->resultItem,$this->results);
    }
	
	/**
     * @test
     */
    function will_filter_data_if_filter_data_is_true_and_value_is_equal() {
        $this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"is_verified","value"=>"1","type"=>ADD_Query_Filter::ENCOUNTER));
        $data = array("dac-base-medhistory"=>array(
			"1"=>array(
				"encounter"=>array("id"=>1,"is_verified"=>0),
				"observations"=>array("atopic"=>"N")
			))
		);
        $this->SUT->filterEncounters($this->resultItem,$this->results);
    }
	
	/**
     * @test
     */
    function will_ignore_data_if_filter_data_is_true_and_value_is_equal() {
        $this->SUT = new ADD_Query_Filter_EncValue(array("op"=>"equal","field"=>"is_verified","value"=>"1","filter_data"=>true));
        $data = array("dac-base-medhistory"=>array(
			"1"=>array(
				"encounter"=>array("id"=>1,"is_verified"=>1),
				"observations"=>array("atopic"=>"N")
			))
		);
		$oldData = $data;
        $this->SUT->filterEncounters($this->resultItem,$this->results);
    }
}