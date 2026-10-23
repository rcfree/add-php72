<?php

class Database_PatientTest extends PHPUnit_Framework_TestCase
{	
	protected static $pdb;
	protected static $edb;
	protected static $pat;
        
	static function setUpBeforeClass() {
		global $home;
		$add = ADD::sys();
		
		self::$edb = ADD::db('encounter');
		self::$pdb = ADD::db('patient');
		//self::$pat = self::$pdb->createPatient(array('first_name'=>'JOHN','last_name'=>'CONNOR','date_of_birth'=>'1972-12-03'));
	}
	/**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Cannot create patient. No identifier field was provided
         */
        public function testPatientCreateFailsIfNull() {
            self::$pdb->createPatientDemographics(null);
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Cannot create patient. No identifier field was provided
         */
        public function testPatientCreateFailsIfOnlyNames() {
            self::$pdb->createPatientDemographics(array("first_name"=>"JEFF","last_name"=>"BRIDGES"));
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Cannot create patient. No first_name field was provided
         */
        public function testPatientCreateFailsIfNoNamesButId() {
            self::$pdb->createPatientDemographics(array("identifier"=>"S1234567"));
        }
        
         /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Cannot create patient. No last_name field was provided
         */
        public function testPatientCreateFailsIfFirstNameAndId() {
            self::$pdb->createPatientDemographics(array("identifier"=>"S1234567","first_name"=>"JAMES"));
        }
        
        public function testMalePatientCreateReturnsExpectedData() {
            $initData = array("identifier"=>"S1234567","first_name"=>"JAMES","last_name"=>"HERRIOT","date_of_birth"=>"20/03/1946","gender"=>"M","ethnic_group"=>"C");
            list($cpat,$caddr,$cgp) = self::$pdb->createPatientDemographics($initData);
            list($gpat,$gaddr,$ggp) = self::$pdb->getPatientDemographics($cpat['id']);
            
            $expData = array_merge($initData,array(
                'id' => '1',
                'title' => null,
                'middle_name' => null,
                'hospital_no' => null,
                'nhs_no' => null,
                'born_in_uk' => null,
                'country_of_birth' => null,
                'marital_status' => null,
                'deceased' => null,
                'date_of_death' => null,
                'address_id' => null,
                'gp_id' => null,
                'is_deleted' => '0',
            ));
            $this->assertValidTimestamp($gpat['date_created']);
            $this->assertValidTimestamp($gpat['date_updated']);
            unset($gpat['date_created']);
            unset($gpat['date_updated']);
            $this->assertEquals($expData,$gpat);
        }
        
        public function testFemalePatientCreateReturnsExpectedData() {
            $initData = array("identifier"=>"S1234568","first_name"=>"JANE","last_name"=>"HERRIOT","date_of_birth"=>"20/03/1946","gender"=>"F","ethnic_group"=>"Z");
            list($cpat,$caddr,$cgp) = self::$pdb->createPatientDemographics($initData);
            list($gpat,$gaddr,$ggp) = self::$pdb->getPatientDemographics($cpat['id']);
            
            $expData = array_merge($initData,array(
                'id' => '2',
                'title' => null,
                'middle_name' => null,
                'hospital_no' => null,
                'nhs_no' => null,
                'born_in_uk' => null,
                'country_of_birth' => null,
                'marital_status' => null,
                'deceased' => null,
                'date_of_death' => null,
                'address_id' => null,
                'gp_id' => null,
                'is_deleted' => '0',
             ));
            $this->assertValidTimestamp($gpat['date_created']);
            $this->assertValidTimestamp($gpat['date_updated']);
            unset($gpat['date_created']);
            unset($gpat['date_updated']);
            $this->assertEquals($expData,$gpat);
        }
        
        public function testDeceasedPatientReturnsExpectedData() {
            $initData = array("identifier"=>"S1234562","first_name"=>"JANE","last_name"=>"HERRIOT","date_of_birth"=>"20/03/1946","gender"=>"F","ethnic_group"=>"Z",'deceased' => '1','date_of_death' => '02/04/2013');
            list($cpat,$caddr,$cgp) = self::$pdb->createPatientDemographics($initData);
            list($gpat,$gaddr,$ggp) = self::$pdb->getPatientDemographics($cpat['id']);
            
            $expData = array_merge($initData,array(
                'id' => '3',
                'title' => null,
                'middle_name' => null,
                'hospital_no' => null,
                'nhs_no' => null,
                'born_in_uk' => null,
                'country_of_birth' => null,
                'marital_status' => null,
                'address_id' => null,
                'gp_id' => null,
                'is_deleted' => '0',
             ));
            $this->assertValidTimestamp($gpat['date_created']);
            $this->assertValidTimestamp($gpat['date_updated']);
            unset($gpat['date_created']);
            unset($gpat['date_updated']);
            $this->assertEquals($expData,$gpat);
        }
        
        public function testPatientWithLongNameFails() {
            $initData = array("identifier"=>"S1234569","first_name"=>"JANE-ANNE MCDONALDY PERSON WITH A VERY LONG NAME RIDICULOUSLY LONG IN FACT","last_name"=>"HERRIOT","date_of_birth"=>"20/03/1946","gender"=>"F","ethnic_group"=>"Z");
            list($cpat,$caddr,$cgp) = self::$pdb->createPatientDemographics($initData);
        }
        
        /**
         * @expectedException ADD_PatientWithDuplicate
         * @expectedExceptionMessage Unable to create patient. Another patient containing one or more of the same unique IDs was found in ADD.
         */
        public function testPatientWithDuplicateIdentifierFails() {
            $initData = array("identifier"=>"S1234568","first_name"=>"JANE-ANNE MCDONALD","last_name"=>"HERRIOT","date_of_birth"=>"20/03/1946","gender"=>"F","ethnic_group"=>"Z");
            list($cpat,$caddr,$cgp) = self::$pdb->createPatientDemographics($initData);
        }
        
        function assertValidTimestamp($value) {
            if (strlen($value)==0) throw new Exception("Invalid timestamp '$value'");
            $dt = DateTime::createFromFormat("Y-m-d H:s:i",$value,new DateTimeZone(date_default_timezone_get()));
            if (!$dt) throw new Exception("Invalid timestamp '$value'");
        }
	
}

?>