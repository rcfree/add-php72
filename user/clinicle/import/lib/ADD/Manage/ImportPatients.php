<?php





class ADD_Manage_ImportPatients implements ADD_ManageInterface {
	function getOptsList() {
		return "c:l:";
	}

	function getDescription() {
		return "Import patients into ADD";
	}
	
	function manage($options) {
		if (!isset($options['c'])) die("No 'config' file given\n");
		$config = new ADD_Config();
		$config->loadFile($options['c']);
		
		$context = new ADD_PatientImport_Context($config);
		$pmi = ADD_Util::createPMI();
		$matchResultFactory = new ADD_PatientImport_MatchResultFactory();
		$pmiMatcher = new ADD_PatientImport_PmiMatcher($pmi,$matchResultFactory);
		
		$pl = new ADD_PatientImport($context,$pmiMatcher);
		
		$pl->run();
	}
}
?>
