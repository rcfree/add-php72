<?php


class ADD_ModuleInstall_Dictionary_Plugin_ReadCodesUpdate implements ADD_ModuleInstall_Dictionary_PluginInterface {
	private $drugWeights=array();
	function afterGetFields(&$fields) {
		$fields = array("term_key","uniquifier","term_30","term_60","term_198","term_code","language_code","read_code_drug","status_flag");
	}
	function beforeProcessRow(&$row) {
		if (!preg_match("/^[1-9a-zA-Z]{4}\.{1}$/",$row['read_code_drug'])) {
			$row = null;
			return;
		}
		
		if(!ADD_Util::isEmpty($row['term_198'])) {
			$row['title']=$row['term_198'];
		}
		else if(!ADD_Util::isEmpty($row['term_60'])) {
			$row['title']=$row['term_60'];
		}
		else if(!ADD_Util::isEmpty($row['term_30'])) {
			$row['title']=$row['term_30'];
		}
		$row['value']=$row['read_code_drug'];
		if($row['status_flag']==1) {
			$row['disabled']=1;
		}
		$row['dictionary']="read_codes";
		$row['allow_change']=1;
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