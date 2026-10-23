<?php

class Database_EncounterTest extends PHPUnit_Framework_TestCase
{
	protected static $pdb;
	protected static $edb;
	protected static $pat;
	
	protected $initialData = array(
			'date_first_seen' => '03/01/2012',
			'occu_cause_worsening' => '1',
			'height' => '120',
			'weight' => '85',
			'fat_free_mass' => '5',
			'informed_consent' => '1',
            );
        protected $multiData = array(
            'animal_exposure'=>'Y',
            'animal_type'=>array('3'=>'3','4'=>'4','5'=>'5'),
            'exposure_to_aspergillus_other'=>'N',
            'other_significant_exposure'=>'N'
          );
        
        protected $failData = array(
            'date_last_seen' => '03/01/2012',
            'occu_cause_worsening' => '1',
            'height' => '120',
            'weight' => '85',
            'fat_free_mass' => '5',
            'informed_consent' => '1',
          );
        
        protected $longData = array(
            'date_first_seen' => '03/01/2012',
            'occu_cause_worsening' => '1',
            'working_status'=>'5',
            'not_working_other_cause' => 'This is a particularly long string which is unlikely to be the case in actual fact because this is not a comments box actually. This is a particularly long string which is unlikely to be the case in actual fact because this is not a comments box actually. Another cause as well',
            'height' => '120',
            'weight' => '90',
            'fat_free_mass' => '2',
            'informed_consent' => '1',
          );
        
        protected $nonEscapedData = array(
            'date_first_seen' => '03/01/2012',
            'occu_cause_worsening' => '1',
            'working_status'=>'5',
            'not_working_other_cause' => "This isn't a (particularly) long string \"!@£$%^&*",
            'height' => '120',
            'weight' => '85',
            'fat_free_mass' => '5',
            'informed_consent' => '1',
          );
        
	static function setUpBeforeClass() {
		global $home;
		$add = ADD::sys();
		
		self::$edb = ADD::db('encounter');
		self::$pdb = ADD::db('patient');
                list($pat,$addr,$gp) = self::$pdb->createPatientDemographics(array('identifier'=>'S87654321','first_name'=>'JOHN','last_name'=>'CONNOR','date_of_birth'=>'03/12/1972'));
		self::$pat = $pat;
        }
        
        static function tearDownAfterClass() {
            self::$edb->purgePatientEncounters(self::$pat);
            self::$pdb->purgePatient(self::$pat);
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Cannot update null encounter
         */
        function testUpdateEncounterObservationsWithNoEncounterFails() {
            self::$edb->updateEncounterObservations(null,array());
            
        }
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Encounter record with ID 'X' does not exist
         */
        function testUpdateEncounterObservationsWithNoneExistingEncounterFails() {
            self::$edb->updateEncounterObservations(array("id"=>"X"),array());
            
        }
        
        function testUpdateEncounterObservationsWithEmptyObservationsIsNotUpdated() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            self::$edb->updateEncounterObservations($enc,array());
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(0,$obs);
        }
        
        function testUpdateEncounterObservationsWithSimpleDataReturnsCorrectObservations() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->initialData);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(6,$obs);
            $this->assertEquals($this->initialData,$obs);
        }
        
        function testUpdateEncounterObservationsWithMultiDataReturnsCorrectObservations() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-exposure",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->multiData);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(4,$obs);
            $this->assertEquals($this->multiData,$obs);
        }
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Observation field 'date_last_seen' is not defined in encounter type 'dac-base-details'
         */
        function testUpdateEncounterObservationsWithIncorrectObservationTypeFails() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->failData);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(6,$obs);
            $this->assertEquals($this->failData,$obs);
        }
        
        function testUpdateEncounterObservationsWithLongDataIsCorrect() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->longData);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(8,$obs);
            $this->assertEquals($this->longData,$obs);
        }
        
        function testUpdateEncounterObservationsWithNoneEscapedDataIsCorrect() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->nonEscapedData);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertCount(8,$obs);
            $this->assertEquals($this->nonEscapedData,$obs);
        }
        
        function testUpdateEncounterObservationsReturnsCreatedObservations() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",array());
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->longData);
            $this->assertCount(8,$created);
            $this->assertCount(0,$updated);
            //$this->assertEquals($this->longData,$obs);
        }
        
        function testUpdateEncounterObservationsUpdatesCorrectly() {
            list($enc,$obs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],"dac-base-details",$this->initialData);
            $this->assertCount(6,$obs);
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->longData);
            $this->assertCount(2,$created);
            $this->assertCount(2,$updated);
            list($created,$updated) = self::$edb->updateEncounterObservations($enc,$this->nonEscapedData);
            $this->assertCount(0,$created);
            $this->assertCount(3,$updated);
            $obs = self::$edb->getObservationsInEncounter($enc);
            $this->assertEquals($this->nonEscapedData,$obs);
        }
        
        function testCreateEncounterWorksIfPatientExists() {
            list($benc,$bobs) = self::$edb -> createEncounterWithTypeAndObservations(self::$pat['id'],'dac');
            $expData = array (
                'patient_id' => '1',
                'encounter_form_id' => '1',
                'revision_no' => 1,
                'date_created' => self::$edb->now(),
                'date_updated' => self::$edb->now(),
                'id' => '9',
            );
            $this->assertEquals($expData,$benc);
            $this->assertCount(0,$bobs);
           // $this->assertEquals(array("id"=>1,"encounter_form_id"=>
        }
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Patient with ID 'X' not found in the database
         */
        function testCreateEncounterFailsIfPatientDoesNotExist() {
            list($benc,$bobs) = self::$edb -> createEncounterWithTypeAndObservations("X",'dac');
            
        }
        
         /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Patient with ID '' not found in the database
         */
        function testCreateEncounterFailsIfPatientIsNull() {
            list($benc,$bobs) = self::$edb -> createEncounterWithTypeAndObservations(null,'dac');
            
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage The encounter type 'duck' is not defined
         */
        function testCreateEncounterFailsIfEncTypeDoesNotExist() {
            list($benc,$bobs) = self::$edb -> createEncounterWithTypeAndObservations(self::$pat['id'],'duck');
        }
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage No parent encounter provided
         */
        function testCreateChildEncounterFailsIfNoParent() {
            list($enc,$obs) = self::$edb -> createChildEncounterWithTypeAndObservations(null,'dac-base',array());
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage The encounter type 'duck-base' is not defined
         */
        function testCreateChildEncounterFailsIfEncTypeDoesNotExist() {
            $penc = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],'dac');
            list($enc,$obs) = self::$edb -> createChildEncounterWithTypeAndObservations($penc,'duck-base',array());
        }
        
        /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Parent encounter with ID 'X' does not exist
         */
        function testCreateChildEncounterFailsIfParentDoesNotExist() {
            $penc = array("id"=>"X","patient_id"=>self::$pat['id']);
            list($enc,$obs) = self::$edb -> createChildEncounterWithTypeAndObservations($penc,'dac-base',array());
        }
        
         /**
         * @expectedException ADD_DatabaseException
         * @expectedExceptionMessage Patient with ID 'X' does not exist
         */
        function testCreateChildEncounterFailsIfPatientDoesNotExist() {
            list($penc,$pobs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],'dac');
            
            $penc['patient_id']="X";
            list($enc,$obs) = self::$edb -> createChildEncounterWithTypeAndObservations($penc,'dac-base',array());
        }        

        function testCreateChildEncounterReturnsCorrectEncounter() {
            list($penc,$pobs) = self::$edb->createEncounterWithTypeAndObservations(self::$pat['id'],'dac');
            list($enc,$obs) = self::$edb -> createChildEncounterWithTypeAndObservations($penc,'dac-base-details',$this->initialData);
            $this->assertCount(6,$obs);
        }
       
        
}

?>