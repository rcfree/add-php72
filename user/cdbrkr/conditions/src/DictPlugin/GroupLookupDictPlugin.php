<?php
namespace Codebreaker\Conditions\DictPlugin;
use ClinicLE\Install\Dictionary\DictPluginInterface;
use CLE;
class GroupLookupDictPlugin implements DictPluginInterface {
	private $_groupCount = array();	
	function beforeImport() {
	}
    function beforeProcessRow(&$row) {
    	$db = CLE::db('BASE');
		$groupId = $db->fetchSingleValue("select id from icd10_group where value=?",$row['group']);
    	$row['icd10_group_id']=$groupId;
    }
    function afterProcessRow(&$row) {
    	
    }
    function afterGetFields(&$row) {}
    function afterImport($core) {
        
    }
    
    function processFields(&$fields) {
        
    }
}