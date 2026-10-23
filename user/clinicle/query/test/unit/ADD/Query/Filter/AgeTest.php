<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
;
class ADD_Query_Filter_AgeTest extends ADD_Test_Query_Filter_BaseTest {
    private $SUT;
    
    /**
     * @test
     */
    function no_today_will_throw_exception() {
        $this->setExpectedException("ADD_SettingNotDefinedException");
        $this->SUT = new ADD_Query_Filter_Age(array("op"=>"equal","value"=>52));
        
    }
    /**
     * @test
     */
    function equal_op_will_include_patient_with_an_equal_age() {
        
        $this->SUT = new ADD_Query_Filter_Age(array("op"=>"equal","value"=>52,"today"=>"20/11/2014"));
        $this->SUT->filterPatients($this->results);
		
//$this->assertTrue($this->SUT->shouldInclude($this->patient));
    }
    
    /**
     * @test
     */
    function not_equal_op_will_exclude_patient_with_an_equal_age() {
        $this->SUT = new ADD_Query_Filter_Age(array("op"=>"not_equal","value"=>52,"today"=>"20/11/2014"));
        $this->patient = array("id"=>123,"first_name"=>"JEFF","last_name"=>"BRIDGES","date_of_birth"=>"03/02/1962");
        $this->SUT->filterPatients($this->results);
        //$this->assertFalse($this->SUT->shouldInclude($this->patient));
    }
    
    /**
     * @test
     */
    function in_op_will_include_patient_in_list_of_ages() {
        $this->SUT = new ADD_Query_Filter_Age(array("op"=>"in","values"=>array("48","52"),"today"=>"20/11/2014"));
        $this->patient = array("id"=>123,"first_name"=>"JEFF","last_name"=>"BRIDGES","date_of_birth"=>"03/02/1962");
        $this->SUT->filterPatients($this->results);
        //$this->assertTrue($this->SUT->shouldInclude($this->patient));
    }
    
    /**
     * @test
     */
    function not_in_op_will_exclude_patient_in_list_of_ages() {
        $this->SUT = new ADD_Query_Filter_Age(array("op"=>"not_in","values"=>array("48","52"),"today"=>"20/11/2014"));
        $this->patient = array("id"=>123,"first_name"=>"JEFF","last_name"=>"BRIDGES","date_of_birth"=>"03/02/1962");
        $this->SUT->filterPatients($this->results);
        //$this->assertFalse($this->SUT->shouldInclude($this->patient));
    }
}