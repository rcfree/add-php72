<?php
namespace ADD\ResphyImport;
class MatchScorer {
	public function __construct(\LRB_PatientLookup $patientLookup, \ADD_Import_PatientMatcher $patientMatcher) {
		$this->_patientLookup = $patientLookup;
		$this->_patientMatcher = $patientMatcher;
	}
	public function scorePatientMatch(\ADD_RespPhys_TestFile $testFile) {
		$identifier = $testFile->getIdentifier();
		$pmiPatient = null;
		
		try {
			$pmiPatient = $this->_patientLookup->get_patient_by_id($identifier);
		}
		catch(Exception $ex) {
			if(preg_match("/identifier .+ was not found/",$ex->getMessage())) {
				ADD::log()->warn("IGNORE: patient ID '$identifier' not present in PMI");
				return 0;
			}
			return 0;
		}
		
		return $this->_patientMatcher->calculateScore($identifier,$testFile->getFirstName(),$testFile->getLastName(),$testFile->getDateOfBirth(),$pmiPatient);
	}
}