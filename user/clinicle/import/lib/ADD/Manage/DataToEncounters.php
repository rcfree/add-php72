<?php



class ADD_Manage_DataToEncounters implements ADD_ManageInterface {
	function getOptsList() {
		return "c:l:";
	}

	function getDescription() {
		return "Convert data to encounters for importing into ADD";
	}
	
	function manage($options) {

		$opts = getopt("c:l:");
		if (!isset($opts['c'])) die("No 'config' file given\n");
		
		$config = parse_ini_file($opts['c'],true);
		
		$eo = new ADD_Import_EncounterOutput($config['datatoenc']);
		$eo->run();
	}
}
?>