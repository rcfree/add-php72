<?php
class ADD_PatientImport {
	private $_context;
	
	function __construct(ADD_PatientImport_Context $context, ADD_PatientImport_PmiMatcher $pmiMatcher) {
		$this->_context = $context;
		$this->_pmiMatcher = $pmiMatcher;
	}
	function getContext() {
		return $this->_context;
	}
	function getPmiMatcher() {
		return $this->_pmiMatcher;
	}
	function run() {
		$this->_context->prepare($this);
		
		$config = $this->_context->getConfig();
		
		$this->_failScore = $config->assertSetting('failScore');
		$this->_totalFailScore = $config->assertSetting('totalFailScore');
		
		$this -> _sourceToTarget();
		
		$this->_context->destroy();
	}
	
	function _sourceToTarget() {
		$log = ADD::log($this);
		$plugins = $this->_context->getPlugins();
		$source = $this->_context->getSource();
		
		//$plugins -> callMethod("start", $source);
		
		//loop through each row in the input file
		while ($sourcePatient = $source -> nextPatient()) {
			$target->beforePatientAction($this);
			
			//$plugins -> callMethod("afterRead", $sourcePatient);
			
			$log -> info("## Patient $patientId - $fname, $lname, $dob");
			
			$fail = false;
			$issues = array();
			
			$matchResult = $pmiMatcher->determineMatchResult($sourcePatient);
			
			if ($this->isBelowThreshold($matchResult)) {
				$this->_target->outputFailPatient($matchResult);
				continue;
			}
			
			$this->_target->outputSuccessPatient($matchResult);
			
		}
	}
	
	function isBelowThreshold() {
		if($this->_fscore < $this->_failScore|| $this->_lscore < $this->_failScore || $this->getTotalScore()<$this->_totalFailScore) {
			return true;
		}
		return false;
	}
}
?>
