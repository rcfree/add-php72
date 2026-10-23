<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
;

class ADD_Query_RunTest extends ADD_BaseTestFixture {
    private $SUT;
    const IDENTIFIER="S12345678";
    const IDENTIFIER2="S12345679";
    function setUp() {
        $this->SUT = new ADD_Query_Run();
		$this->result = $this->getMock("ADD_Query_Result");
        $this->initSystemAccess(false,array("patient","encounter"));
        
    }
    
    function expectEncounters() {
        ADD::db('encounter')->expects($this->at(1))->method("fetchColumn")->will($this->returnValue(array(
            1,2
        )));
        ADD::db('encounter')->expects($this->at(2))->method("fetchColumn")->will($this->returnValue(array(
            2
        )));
        ADD::db('encounter')->expects($this->at(3))->method("fetchColumn")->will($this->returnValue(array(
            2
        )));
        ADD::db('encounter')->expects($this->at(4))->method("fetchAll")->will($this->returnValue(array(
            array("encounter_id"=>1,"name"=>"atopic","value"=>"N"),
        )));
    }
    /**
     * @test
     */
    function it_will_create_and_add_a_filter_class_to_the_results() {
        $this->SUT->addFilter("Age",array("op"=>"in","values"=>array("5","20"),"today"=>"20/11/2014"));
        $filters = $this->SUT->getFilters();
        $this->assertEquals("ADD_Query_Filter_Age",get_class($filters[0]));
        $this->assertEquals("in",$filters[0]->getAttr("op"));
        $this->assertEquals(array("5","20"),$filters[0]->getAttr("values"));
    }
    function expectObsField() {
        ADD::db('encounter')->expects($this->once())->method("getFieldFromEncounterForm")->with("dac-base-medhistory","atopic")->will($this->returnValue(array(
          "id"=>1,"name"=>"atopic","encounter_form_id"=>1  
        )));
    }
    function expectPatient() {
        ADD::db('patient')->expects($this->once())->method("getPatient")->with(2)->will($this->returnValue(array("first_name"=>"JOHN","last_name"=>"SMITH","gender"=>"M")));
    }
    /**
     * @test
     */
    function it_will_run() {
		$this->markTestSkipped();
        $this->expectEncounters();
        $this->expectPatient();
        $this->expectObsField();
        $this->SUT->addFilter("Age",array("op"=>"equal","values"=>array("5","20"),"today"=>"20/11/2014"));
        $this->SUT->addFilter("ObsValue",array("op"=>"equal","value"=>"Y","encounter"=>"dac-base-medhistory","field"=>"atopic"));
        $this->SUT->addValue("ObsData",array("encounter"=>"dac-base-medhistory"));
        $this->SUT->addValue("PatientData",array("fields"=>array("first_name","last_name","gender")));
        //$this->SUT->run($this->result);
    }
}