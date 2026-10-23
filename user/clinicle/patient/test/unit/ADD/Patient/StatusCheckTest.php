<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
set_include_path(getenv('ADD_MODULE_PATH')."/lib:".get_include_path());
require_once("ADD/Patient/StatusCheck.php");

class ADD_Patient_StatusCheckTest extends ADD_BaseTestFixture {
	public function setUp() {
		$this->SUT = new ADD_Patient_StatusCheck();
		$this->obsPagData = array (
			array (
			  'id' => '87',
			  'identifier' => 'S0000003',
			  'title' => 'MR',
			  'first_name' => 'JOHN',
			  'middle_name' => '',
			  'last_name' => 'WATSON',
			  'date_of_birth' => '03/04/1930',
			  'hospital_no' => '',
			  'nhs_no' => '9876553',
			  'born_in_uk' => '',
			  'country_of_birth' => '',
			  'gender' => 'M',
			  'marital_status' => 'S',
			  'ethnic_group' => 'A',
			  'deceased' => '0',
			  'date_of_death' => NULL,
			  'address_id' => '250',
			  'gp_id' => '70',
			  'is_deleted' => '0',
			  'date_created' => '2014-11-12 12:43:32',
			  'date_updated' => '2014-11-12 12:43:32',
			),
			array (
			  'id' => '250',
			  'address_line_1' => '221B BAKER STREET',
			  'address_line_2' => 'MARYLEBONE',
			  'address_line_3' => 'LONDON',
			  'address_line_4' => '',
			  'postcode' => 'W1',
			  'home_phone' => '56',
			  'work_phone' => '413123',
			  'patient_id' => '87',
			  'date_created' => '2014-11-12 12:43:32',
			  'date_updated' => '2014-11-12 12:43:32',
			),
			array (
			  'id' => '70',
			  'local_code'=>'GLO',
			  'doh_code'=>'G1234567',
		      'practice_code'=>'C83232',
			  'gp_title' => 'MR',
			  'gp_forename' => 'SHERLOCK',
			  'gp_surname' => 'HOLMES',
			  'gp_address1' => '221B BAKER STREET',
			  'gp_address2' => 'MARYLEBONE',
			  'gp_address3' => 'LONDON',
			  'gp_address4' => '',
			  'gp_postcode' => 'W1',
			  'gp_workphone' => 'GP_WORKPHONE',
			  'date_created' => '2014-11-12 12:43:32',
			  'date_updated' => '2014-11-12 12:43:32',
			),
		  );
		$this->obsPmiData = array (
		  'main_pat_id' => 'S0000003',
		  'title'=>'MR',
		  'last_name' => 'WATSON',
		  'middle_name' => '',
		  'first_name' => 'JOHN',
		  'dob' => '03/04/1930',
		  'marital_status' => 'S',
		  'gender' => 'M',
		  'ethnicity' => 'A',
		  'nhs_number' => '9876553',
		  'gp_title' => 'MR',
		  'gp_forename' => 'SHERLOCK',
		  'gp_surname' => 'HOLMES',
		  'gp_addr1' => '221B BAKER STREET',
		  'gp_addr2' => 'MARYLEBONE',
		  'gp_addr3' => 'LONDON',
		  'gp_addr4' => '',
		  'gp_postcode' => 'W1',
		  'gp_workphone' => 'GP_WORKPHONE',
		  'gp_local_code'=>'GLO',
		  'gp_doh_code'=>'G1234567',
		  'gp_practicecode'=>'C83232',
		  'pat_addr1' => '221B BAKER STREET',
		  'pat_addr2' => 'MARYLEBONE',
		  'pat_addr3' => 'LONDON',
		  'pat_addr4' => '',
		  'postcode' => 'W1',
		  'homephone' => '56',
		  'workphone' => '413123',
		  'hospital_no' => 'R221221',
		  'date_of_death'=>'',
		  'death_indicator'=>'0'
		);
		
	}
	
	/**
	 * @test
	 */
	public function getAllChangedFields_returns_false_if_patient_is_the_same() {
		$obsValue = array();
		$this->assertEquals($obsValue,$this->SUT->getAllChangedFields($this->obsPagData,$this->obsPmiData));
	}
	
	/**
	 * @test
	 */
	public function getAllChangedFields_returns_changed_fields_if_patient_has_changed() {
		$changedPmiData = $this->obsPmiData;
		$changedPmiData['death_indicator']=1;
		$obsValue = array("deceased"=>1);
		$this->assertEquals($obsValue,$this->SUT->getAllChangedFields($this->obsPagData,$changedPmiData));
	}
	
	/**
	 * @test
	 */
	public function getAllChangedFields_returns_changed_fields_if_address_has_changed() {
		$changedPmiData = $this->obsPmiData;
		$changedPmiData['postcode']="W1 3RH";
		$obsValue = array("postcode"=>"W1 3RH");
		$this->assertEquals($obsValue,$this->SUT->getAllChangedFields($this->obsPagData,$changedPmiData));
	}
	
	/**
	 * @test
	 */
	public function getAllChangedFields_returns_changed_fields_if_gp_has_changed() {
		$changedPmiData = $this->obsPmiData;
		$changedPmiData['gp_local_code']="LON";
		$changedPmiData['gp_doh_code']='G1234568';
		$changedPmiData['gp_practicecode']='C83234';
		$obsValue = array("local_code"=>"LON","doh_code"=>"G1234568","practice_code"=>"C83234");
		$this->assertEquals($obsValue,$this->SUT->getAllChangedFields($this->obsPagData,$changedPmiData));
	}
	
	/**
	 * @test
	 */
	public function getAllChangedFields_returns_changed_fields_if_both_patient_and_address_have_changed() {
		$changedPmiData = $this->obsPmiData;
		$changedPmiData['death_indicator']=1;
		$changedPmiData['postcode']="W1 3RH";
		
		$obsValue = array("deceased"=>1,"postcode"=>"W1 3RH");
		$this->assertEquals($obsValue,$this->SUT->getAllChangedFields($this->obsPagData,$changedPmiData));
	}
	
	
	/**
	 * @test
	 */
	public function changedPatient_returns_false_if_no_changes() {
		$this->assertFalse($this->SUT->changedPatient($this->obsPagData,$this->obsPmiData));
	}
	
	/**
	 * @test
	 */
	public function changedPatient_returns_expected_changed_patient_data() {
		$obsValue = array();
		foreach($this->obsPagData as $itemData) {
			$obsValue = $obsValue + $itemData;
		}
		$obsValue['deceased']=1;
		$obsValue['postcode']="W1 3RH";
		$obsValue['local_code']="LON";
		
		$changedPmiData = $this->obsPmiData;
		$changedPmiData['death_indicator']=1;
		$changedPmiData['postcode']="W1 3RH";
		$changedPmiData['gp_local_code']="LON";
		
		$this->assertEquals($obsValue,$this->SUT->changedPatient($this->obsPagData,$changedPmiData));
	}
}