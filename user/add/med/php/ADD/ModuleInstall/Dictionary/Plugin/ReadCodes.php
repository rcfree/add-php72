<?php


class ADD_ModuleInstall_Dictionary_Plugin_ReadCodes implements ADD_ModuleInstall_Dictionary_PluginInterface {
	private $drugWeights=array();
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
		if (!empty($row['strength'])) $row['title'].=" ".$row['strength'];
		if (!empty($row['formulation'])) $row['title'].=" ".$row['formulation'];
		
		$row['concept']=$row['value'];
		$row['weight']=isset($this->drugWeights[$row['value']]) ? $this->drugWeights[$row['value']] : null;
	}
	function beforeImport($dataFile) {
		$dataFile->setFieldSep("|");
		$dataFile->setTrimFields(true);
		$commonDrugs = new LRB_DataFile_Delimited(dirname(__FILE__)."/../../../../../dictionary/common_drugs.txt");
		$commonDrugs->setHeadings(array("readcode"));
		$commonDrugs->open();
		$commonCount=1;
		while($row = $commonDrugs->nextRow()) {
			$this->drugWeights[$row['readcode']]=$commonCount;
			$commonCount++;
		}
	}

    public function afterProcessRow(&$row) {
        
    }

    public function processFields(&$fields) {
        
    }
    public function afterImport($core) { }

}