<?php
require_once("ADD/ModuleBundle/DictLoad/Plugin.php");

class ADD_ModuleBundle_DictLoad_Plugin_ReadCodes implements ADD_ModuleBundle_DictLoad_Plugin {
	function afterGetFields(&$fields) {
		$fields = array('value','drug_name','strength','formulation');
	}
	function beforeProcessRow(&$row) {
		if (!preg_match("/^[1-9a-zA-Z]{4}\.{1}$/",$row['value'])) {
			$row = null;
			return;
		}
		$row['data']=ADD_Util::serialize($row);
		if (!empty($row['drug_name'])) $row['title']=$row['drug_name'];
		if (!empty($row['formulation'])) $row['title'].=" ".$row['formulation'];
		if (!empty($row['strength'])) $row['title'].=" ".$row['strength'];
		
		$row['concept']=$row['value'];
	}
	function beforeImport($dataFile) {
		$dataFile->setFieldSep("|");
		$dataFile->setTrimFields(true);
	}

    public function afterProcessRow(&$row) {
        
    }

    public function processFields(&$fields) {
        
    }

}