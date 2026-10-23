<?php


class ADD_ModuleInstall_Dictionary_Plugin_PRDiagnoses implements ADD_ModuleInstall_Dictionary_PluginInterface {
    function beforeProcessRow(&$row) {
		$data = array();
		foreach($row as $name => $value) {
			if(ADD_Util::isEmpty($value)) continue;
			if(!preg_match("/icd10/",$name)) continue;
			$data[$name]=$value;
		}
        $row['data']=ADD_Util::serialize($data);
    }
    function afterProcessRow(&$row) {
    }
    function beforeImport($dataFile) {
            $dataFile->setTrimFields(true);
            //$dataFile->setLimit(10);
    }
    function afterGetFields(&$fields) {
            $fields = array('value','deleted','title');
			for($i=1;$i<15;$i++) {
				$fields[]="icd10-$i";
		    }
	}
    function afterImport($core) {
    }
    
   
    public function processFields(&$fields) {
        
    }
}