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
class ADD_Query_Filter_ObsValueTest extends ADD_Test_Query_Filter_BaseTest {
    function setUp() {
		parent::setUp();
		$this->resultItem = $this->getMockBuilder("ADD_Query_ResultItem")->disableOriginalConstructor()->getMock();
	}
	/**
     * @test
     */
    function equal_op_will_include_patient_with_an_equal_obs_value() {
        $this->expectObsField();
        $this->SUT = new ADD_Query_Filter_ObsValue(array("op"=>"equal","encounter"=>"dac-base-medhistory","field"=>"atopic","value"=>"Y"));
        $this->results->expects($this->once())->method("updateMatchingPatients");
        $this->SUT->filterPatients($this->results);
    }
	
	/**
     * @test
     */
    function equal_op_will_include_encounters_with_an_equal_obs_value() {
        $this->expectObsField();
        $this->SUT = new ADD_Query_Filter_ObsValue(array("op"=>"equal","encounter"=>"dac-base-medhistory","field"=>"atopic","value"=>"Y","type"=>ADD_Query_Filter::ENCOUNTER));
        $this->resultItem->expects($this->once())->method("updateMatchingEncounters");
        $this->SUT->filterEncounters($this->resultItem, $this->results);
    }
	
}