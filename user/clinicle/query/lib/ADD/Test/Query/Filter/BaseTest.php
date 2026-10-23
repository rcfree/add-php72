<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
abstract class ADD_Test_Query_Filter_BaseTest extends ADD_BaseTestFixture {
	function setUp() {
        $this->initSystemAccess(false,array("encounter"));
        $this->results = $this->getMock("ADD_Query_Result");
    }
    
    function expectEncounters() {
        ADD::db('encounter')->expects($this->once())->method("fetchColumn")->will($this->returnValue(array(1,2)));
    }
	
	function expectEncounterIds() {
        ADD::db('encounter')->expects($this->once())->method("fetchColumn")->will($this->returnValue(array(5,6)));
    }
	
	 function expectObsField() {
        ADD::db('encounter')->expects($this->once())->method("getFieldFromEncounterForm")->with("dac-base-medhistory","atopic")->will($this->returnValue(array(
          "id"=>1,"name"=>"atopic","encounter_form_id"=>1  
        )));
    }
}
