<?php

class ADD_ModuleInstall_Dictionary_Plugin_AdmissionGrouping implements ADD_ModuleInstall_Dictionary_PluginInterface {
	function beforeImport() {
	}
    function beforeProcessRow(&$row) {
		$row['title']=$row['value'];
		$row['concept']=$row['value']; 
    }
    function afterProcessRow(&$row) {
    }
    
    function afterGetFields(&$fields) {
            $fields = array('n1','n2','n3','n4','n5','n6','value');
    }
    function afterImport($core) {
        
    }
    
   
    public function processFields(&$fields) {
        
    }
}