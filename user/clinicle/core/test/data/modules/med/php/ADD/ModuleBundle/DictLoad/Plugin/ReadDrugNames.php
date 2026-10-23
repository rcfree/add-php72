<?php
include_once("ADD/ModuleBundle/DictLoad/Plugin.php");

class ADD_ModuleBundle_DictLoad_Plugin_ReadDrugNames implements ADD_ModuleBundle_DictLoad_Plugin {
	function afterGetFields(&$fields) {
		$fields = array('value','drug_name');
	}
	function beforeProcessRow(&$row) {
		if (!preg_match("/^[1-9a-z]{3}\.{2}$/",$row['value'])) {
			$row = null;
			return;
		}
		$row['data']=ADD_Util::serialize($row);
		$row['title']=$row['drug_name'];
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