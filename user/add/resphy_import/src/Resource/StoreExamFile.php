<?php
namespace ADD\ResphyImport\Resource;


use ADD\ResphyImport\ExamFile;
use CLE;

class StoreExamFile {
	private $respPhysService;
	private $patientService;
	private $pmiMatcher;
	
	public function __construct(\ADD_PatientImport_PmiMatcher $pmiMatcher, \ADD_Data_PatientService $patientService,
                                RespPhysService $respPhysService) {
		$this->pmiMatcher = $pmiMatcher;
		$this->patientService = $patientService;
		$this->respPhysService = $respPhysService;
	}
	public function store(ExamFile $examFile) {
		$log = CLE::log();
		$sourcePatient = $examFile->getPatientInfo();
		
		$matchResult = $this->pmiMatcher->determineMatchResult($sourcePatient);
		//if not exact match then
		if($matchResult->getTotalScore()<15) {
			$log->warn("Add exam file to holding:".$examFile->getFileName()." ($matchResult)");
			//add test file to holding
			$patient = $this->patientService->retrievePatientWithSpecialIdentifier("RESPHYHELD");
		}
		else {
			$patient = $this->patientService->retrievePatientFromMatchResult($matchResult);
			$log->info("Add exam file to ".$patient['last_name'].",".$patient['first_name'].":".$examFile->getFileName());
		}
		
		$this->respPhysService->addExamFileToPatient($patient,$examFile,$matchResult);
	}
	public function update(ExamFile $examFile, $audit) {
		$this->respPhysService->updateExamFile($audit,$examFile);
		
	}
	function isExamFileImported(ExamFile $examFile) {
		return $this->respPhysService->isExamFileImported($examFile);
	}
}