<?php

class ADD_ModuleInstall_Dictionary_Plugin_AdmissionCodes implements ADD_ModuleInstall_Dictionary_PluginInterface {
	private $_groupCount = array();	
	function beforeImport() {
	}
    function beforeProcessRow(&$row) {
    	$group = $row['RCO'];
    	if(!isset($this->_groupCount[$group])) $this->_groupCount[$group]=0;
		if($this->_groupCount[$group]>=50) {
			$row=null;
			return;
		}
    	$row['value']=$row['Spell Diagnosis Code 5 digit'];
    	$matches=array();
    	$title = preg_match("/\-\s(.+)$/",$row['Spell Primary Diagnosis'],$matches);
		$row['title']= $matches[1];
		
		$row['data']=ADD_Util::serialize(array("frequency"=>$row["Total Of ID_"]));
		$row['group']=$group;
		$row['allow_change']=1;
		$this->_groupCount[$group]++;
    }
    function afterProcessRow(&$row) {
    }
    function afterGetFields(&$row) {}
    function afterImport($core) {
        
    }
    
    function processFields(&$fields) {
        
    }
}