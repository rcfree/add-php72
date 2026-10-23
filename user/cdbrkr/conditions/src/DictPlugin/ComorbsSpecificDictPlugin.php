<?php
namespace Codebreaker\Conditions\DictPlugin;
use ClinicLE\Install\Dictionary\DictPluginInterface;
use CLE;
class ComorbsSpecificDictPlugin implements DictPluginInterface {
	private $_groupCount = array();	
	function beforeImport() {
		$this->_dict = CLE::db('dictionary')->getDictionaryByName("icd10");
	}
    function beforeProcessRow(&$row) {
		
    }
    function afterProcessRow(&$row) {
    	$value = $row['value'];
		$ditem = CLE::db('dictionary')->getDictionaryItemWithValue($this->_dict,$value);
		if(!$ditem) throw new Exception("ICD10 code '$value' not found in database:".print_r($row));
    }
    function afterGetFields(&$row) {}
    function afterImport($core) {
        
    }
    
    function processFields(&$fields) {
        
    }
}