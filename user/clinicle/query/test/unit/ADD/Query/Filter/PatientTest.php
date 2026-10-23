<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;

class ADD_Query_Filter_PatientTest extends ADD_Test_Query_Filter_BaseTest {
    private $SUT;
   
    /**
     * @test
     */
    function no_field_will_throw_exception() {
        $this->setExpectedException("ADD_SettingNotDefinedException");
        $this->SUT = new ADD_Query_Filter_Patient(array("op"=>"equal","value"=>52));
        $this->SUT->filterPatients($this->results);
    }
    
    /**
     * @test
     */
    function equal_op_will_include_patient_with_first_name() {
        $this->SUT = new ADD_Query_Filter_Patient(array("op"=>"equal","value"=>"JOHN","today"=>"20/11/2014","field"=>"first_name"));
        $this->SUT->filterPatients($this->results);
    }
    
    /**
     * @test
     */
    function field_which_is_not_allowed_will_fail() {
        $this->setExpectedException("ADD_FieldException");
        
        $this->SUT = new ADD_Query_Filter_Patient(array("op"=>"equal","value"=>"JOHN","today"=>"20/11/2014","field"=>"not_allowed"));
        
    }
   
}