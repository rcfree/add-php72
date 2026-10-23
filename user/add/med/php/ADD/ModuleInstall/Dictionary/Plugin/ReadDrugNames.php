<?php


class ADD_ModuleInstall_Dictionary_Plugin_ReadDrugNames implements ADD_ModuleInstall_Dictionary_PluginInterface {
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
    public function afterImport($core) { }

}