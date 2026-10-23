<?php

class ADD_ModuleInstall_Dictionary_Plugin_Consultant implements ADD_ModuleInstall_Dictionary_PluginInterface {
	private $_groupCount = array();	
	function beforeImport() {
	}
    function beforeProcessRow(&$row) {
    	$title = $row['title'];
		$temp = explode(" ",$title);
		$lastName = implode(" ",array_slice($temp,2));
		$firstName = implode(" ",array_slice($temp,0,2));
		$row['title']=$lastName.", ".$firstName;
		$row['allow_change']=1;
    }
    function afterProcessRow(&$row) {
    }
    function afterGetFields(&$row) {}
    function afterImport($core) {
        
    }
    
    function processFields(&$fields) {
        
    }
}