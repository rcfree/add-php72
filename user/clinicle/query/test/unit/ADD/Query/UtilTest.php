<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
;

class ADD_Query_Filter_UtilTest extends ADD_BaseTestFixture {
    
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_empty_sql_if_no_id_fields() {
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(null,array());
		$this->assertEquals("",$sql);
		$this->assertEquals(array(),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_single_set_of_id_fields() {
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id"),array("patient_id"=>array(2,4,5)));
		$this->assertEquals(" where patient_id in (?,?,?)",$sql);
		$this->assertEquals(array(2,4,5),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_set_of_id_fields_is_not_array() {
		list($sql,$params) = ADD_Query_Util::generateIdListInSql("patient_id",array(2,4,5));
		$this->assertEquals(" where patient_id in (?,?,?)",$sql);
		$this->assertEquals(array(2,4,5),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_multiple_set_of_id_fields() {
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id","encounter_id"),array("patient_id"=>array(2,4,5),"encounter_id"=>array(1,5,6,7)));
		$this->assertEquals(" where patient_id in (?,?,?) and encounter_id in (?,?,?,?)",$sql);
		$this->assertEquals(array(2,4,5,1,5,6,7),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_one_id_set_is_null() {
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id","encounter_id"),array("patient_id"=>null,"encounter_id"=>array(1,5,6,7)));
		$this->assertEquals(" where encounter_id in (?,?,?,?)",$sql);
		$this->assertEquals(array(1,5,6,7),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_id_set_is_empty() {
		$idLists = array("patient_id"=>array(),"encounter_id"=>array(1,5,6,7));
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id","encounter_id"),$idLists);
		$this->assertEquals(" where patient_id not like '%' and encounter_id in (?,?,?,?)",$sql);
		$this->assertEquals(array(1,5,6,7),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_if_both_id_sets_are_empty() {
		$idLists = array("patient_id"=>array(),"encounter_id"=>array());
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id","encounter_id"),$idLists);
		$this->assertEquals(" where patient_id not like '%' and encounter_id not like '%'",$sql);
		$this->assertEquals(array(),$params);
	}
	
	/**
	 * @test
	 */
    function generateIdListInSql_will_return_correct_sql_and_params_when_additional_where_clause_is_added() {
		$idLists = array("patient_id"=>array(),"encounter_id"=>array(1,5,6,7));
		list($sql,$params) = ADD_Query_Util::generateIdListInSql(array("patient_id","encounter_id"),$idLists,"name=?",array("JEFF"));
		$this->assertEquals(" where patient_id not like '%' and encounter_id in (?,?,?,?) and name=?",$sql);
		$this->assertEquals(array(1,5,6,7,"JEFF"),$params);
	}
}