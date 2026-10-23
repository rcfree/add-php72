<?php

class ADD_ModuleInstall_Dictionary_Plugin_ICD10Code implements ADD_ModuleInstall_Dictionary_PluginInterface {
    private $topLevels = array();
    private $topCounts=array();
    private $codes = array();
    function beforeProcessRow(&$row) {
        $ddb = ADD::db('dictionary');
        
        if (preg_match("/^[A-Z][0-9A-Z]{1,3}$/",$row['value'])) {
            $topCode = $row['value'];
            $this->topLevels[$topCode]=$row['title'];
            if(!isset($this->topCounts[$topCode])) $this->topCounts[$topCode]=0;

        }
		else {
	        $row['concept']=$row['value'];
	        list($topCode,$lowerCode) = explode(".",$row['value']);
	        if($topCode) $groupLabel = $this->topLevels[$topCode];
			$row['group']=$topCode;
        	$row['group_title']=$groupLabel;
			$this->codes[]=$row['value'];
			$this->topCounts[$topCode]++;
		}
        
        $title = $row['value']." - ".$row['title'];
        
        if(!ADD_Util::isEmpty($row['qualifier4'])) $title.=" (".$row['qualifier4'].")";
		if(!ADD_Util::isEmpty($row['qualifier5'])) $title.=" [".$row['qualifier5']."]";
		$row['allow_change']=1;
		$row['title']=$title;
		$row['data']=ADD_Util::serialize($row);
		
       
    }
    function afterProcessRow(&$row) {
    }
    function beforeImport($dataFile) {
            $dataFile->setFieldSep("\t");
            $dataFile->setTrimFields(true);
            //$dataFile->setLimit(10);
    }
    function afterGetFields(&$fields) {
            $fields = array('value','alt_value','usage','usage_uk','title','qualifier4','qualifier5');
    }
    
    
   
    public function processFields(&$fields) {
        
    }
}
