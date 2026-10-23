<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;
;
class ADD_Query_Filter_IdentifiersTest extends ADD_Test_Query_Filter_BaseTest {
    private $SUT;
    const IDENTIFIER1="S12345678";
    const IDENTIFIER2="S87654321";
   
    /**
     * @test
     */
    function equal_op_will_include_patient_with_an_equal_identifier() {
        $this->SUT = new ADD_Query_Filter_Identifiers(array("op"=>"equal","value"=>self::IDENTIFIER1));
        $this->patient = array("id"=>123,"first_name"=>"JEFF","last_name"=>"BRIDGES","date_of_birth"=>"03/02/1962","identifier"=>self::IDENTIFIER1);
        $this->results->expects($this->once())->method("updateMatchingPatients");
        $this->SUT->filterPatients($this->results);
        
    }
    
    /**
     * @test
     */
    function not_equal_op_will_exclude_patient_with_an_equal_identifier() {
        $this->SUT = new ADD_Query_Filter_Identifiers(array("op"=>"not_equal","value"=>self::IDENTIFIER1));
        $this->patient = array("id"=>123,"first_name"=>"JEFF","last_name"=>"BRIDGES","date_of_birth"=>"03/02/1962","identifier"=>self::IDENTIFIER2);
        $this->results->expects($this->once())->method("updateMatchingPatients");
        $this->SUT->filterPatients($this->results);
        
        
    }
    
    
}