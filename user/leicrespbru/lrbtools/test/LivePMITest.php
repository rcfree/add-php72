<?php
set_include_path(get_include_path().":".getenv('BTKIT_PATH')."/src/lib");
include_once("LRBRUTool/PatientLookup.php");
include_once("PHPUnit/Autoload.php");

class PatientLookup_LivePMI_Test extends PHPUnit_Framework_TestCase {
	
	public function testLookupRealNameDOB() {
		$pl = LRBRUTool_PatientLookup::factory();
		$recs = $pl->get_patient_records(array('fname'=>'ADULT','lname'=>'EDITESTPATIENT','dob'=>'12/09/1952'));
		$this->assertTrue(is_array($recs));
		$this->assertEquals(count($recs),2);
		$headings = $recs[0];
		$row = $recs[1];
		$row_info=$row[0];
		$this->assertTrue(is_array($headings));
		$this->assertTrue(is_array($recs[1]));
		$this->assertTrue(is_array($recs[1][0]));
		$this->assertFalse(is_array($recs[1][1]));
		$this->assertEquals($headings[0],"Patient ID");
		$this->assertEquals($headings[5],"Date of Birth");
		$this->assertEquals($row[6],"12/9/1952");
		$this->assertEquals($row_info['main_pat_id'],"S2693300");
		$this->assertEquals($row_info['first_name'],"ADULT");
		$expected = '[["Patient ID","Gender","Last name","First name","Middle name","Date of Birth","Address line 1","Postcode"],[{"main_pat_id":"S2693300","internal_pat_id":"2746227","title":"MR","last_name":"EDITESTPATIENT","first_name":"ADULT","middle_name":"MALE","dob":"12/9/1952","date_of_death":null,"death_indicator":null,"marital_status":"M","ethnicity":"A","gender":"M","pat_addr1":null,"pat_addr2":null,"pat_addr3":null,"pat_addr4":null,"postcode":null,"homephone":null,"workphone":null,"year_of_birth":"1952","gp_local_code":null,"gp_doh_code":null,"gp_practicecode":null,"gp_title":null,"gp_surname":null,"gp_forename":null,"gp_addr1":null,"gp_addr2":null,"gp_addr3":null,"gp_addr4":null,"gp_postcode":null,"gp_workphone":null,"nhs_number":null,"nhs_number_status":"07"},"S2693300","M","EDITESTPATIENT","ADULT","MALE","12/9/1952",null,null]]';
		$this->assertJsonStringEqualsJsonString($expected,json_encode($recs));
		$this->assertEquals($row[1],"S2693300");
		$this->assertEquals($row[2],"M");
	}
	
	public function testLookupRealId() {
		$pl = LRBRUTool_PatientLookup::factory();
		$recs = $pl->get_patient_records(array('id'=>'S2693300'));
		$this->assertTrue(is_array($recs));
		$this->assertEquals(count($recs),2);
		$headings = $recs[0];
		$row = $recs[1];
		$row_info=$row[0];
		$this->assertTrue(is_array($headings));
		$this->assertTrue(is_array($recs[1]));
		$this->assertTrue(is_array($recs[1][0]));
		$this->assertFalse(is_array($recs[1][1]));
		$this->assertEquals($headings[0],"Patient ID");
		$this->assertEquals($headings[5],"Date of Birth");
		$this->assertEquals($row[6],"12/9/1952");
		$this->assertEquals($row_info['main_pat_id'],"S2693300");
		$this->assertEquals($row_info['first_name'],"ADULT");
		$expected = '[["Patient ID","Gender","Last name","First name","Middle name","Date of Birth","Address line 1","Postcode"],[{"main_pat_id":"S2693300","internal_pat_id":"2746227","title":"MR","last_name":"EDITESTPATIENT","first_name":"ADULT","middle_name":"MALE","dob":"12/9/1952","date_of_death":null,"death_indicator":null,"marital_status":"M","ethnicity":"A","gender":"M","pat_addr1":null,"pat_addr2":null,"pat_addr3":null,"pat_addr4":null,"postcode":null,"homephone":null,"workphone":null,"year_of_birth":"1952","gp_local_code":null,"gp_doh_code":null,"gp_practicecode":null,"gp_title":null,"gp_surname":null,"gp_forename":null,"gp_addr1":null,"gp_addr2":null,"gp_addr3":null,"gp_addr4":null,"gp_postcode":null,"gp_workphone":null,"nhs_number":null,"nhs_number_status":"07"},"S2693300","M","EDITESTPATIENT","ADULT","MALE","12/9/1952",null,null]]';
		$this->assertJsonStringEqualsJsonString($expected,json_encode($recs));
		$this->assertEquals($row[1],"S2693300");
		$this->assertEquals($row[2],"M");
	}
	
	/**
	 * @expectedException Exception
	 */
	 
	public function testLookupMissingId() {
		$pl = LRBRUTool_PatientLookup::factory();
		$recs = $pl->get_patient_records(array('id'=>'A12345W'));
	}
	
	/**
	 * @expectedException Exception
	 */
	 
	public function testLookupNoParameters() {
		$pl = LRBRUTool_PatientLookup::factory();
		$recs = $pl->get_patient_records(array());
	}
	
	/**
	 * @expectedException Exception
	 */
	 
	public function testLookupSomeParameters() {
		$pl = LRBRUTool_PatientLookup::factory();
		$recs = $pl->get_patient_records(array('fname'=>'ADULT','lname'=>'EDITESTPATIENT'));
	}
}	
