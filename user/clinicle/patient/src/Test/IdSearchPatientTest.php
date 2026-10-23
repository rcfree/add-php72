<?php
namespace ClinicLE\Patient\Test;

use ClinicLE\Patient\Search\IdPatientSearch;
use ClinicLE\Testing\AbstractBaseTest;
use CLE;

class IdSearchPatientTest extends AbstractBaseTest
{
    private $SUT;
    private $searchPatients;
    private $patientLookup;
    function setUp() {
        $this->initMockSystem();
        $this->patientLookup = $this->createMock(\LRB_PatientLookup::class);
        $this->searchPatients = $this->addMockDatabase("search_patient", \ADD_Database_SearchPatient::class);
        $this->audit = $this->addMockDatabase("audit",\ADD_Database_Audit::class);
        $this->SUT = new IdPatientSearch($this->patientLookup, $this->searchPatients,array("create"=>true));
    }
    function test_it_searches_for_id() {
        $this->SUT->getSearchResults(array("id"=>"S1234567"));
        $this->assertHasWarning("test");
    }
}