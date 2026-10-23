<?php

class ADD_ModuleInstall_Dictionary_Plugin_ConsultantCode implements ADD_ModuleInstall_Dictionary_PluginInterface {
	const UNKNOWN_CONSULTANT= "Consultant Unknown";
	const UNKNOWN_CODE = "C9999998";
	private $specialties = array();
	function beforeImport() {
		$df = new LRB_DataFile_Delimited("/local/dict/consultant_specialty.csv",array("code","description"));
		$df->load();
		foreach($df->getDataItems() as $dataItem) {
			$this->specialties[$dataItem['code']]=$dataItem['description'];
		}
	}
    function beforeProcessRow(&$row) {
		if($row['loc_org_code']!="RWE") {
			$row=null;
			return;
		}
    	$row['title'] = $row['surname'].", ".$row['initials'];
		$row['value']=$row['prac_code'];
		$row['group']=$row['spec_func_code'];
		$row['group_title']=ADD_Util::val($row['spec_func_code'],$this->specialties,"Unknown specialty ".$row['spec_func_code']);
		$row['allow_change']=1;
    }
    function afterProcessRow(&$row) {
    }
    function afterGetFields(&$fields) {
		$fields = array('gmc_code','prac_code','surname','initials','sex','spec_func_code','prac_type','loc_org_code');
	}
    function afterImport($core) {
        $target = $core->getTarget();
		$dict = $core->getDictionary($core->getName());
		$unknownCons = $target->getItemWithValue($dict,self::UNKNOWN_CODE);
		if($unknownCons) {
			return;
		}
		$dictItem=array("value"=>self::UNKNOWN_CODE,"title"=>self::UNKNOWN_CONSULTANT);
		$target->createNewItem($dict,$dictItem,array("id"=>null));
    }
    
    function processFields(&$fields) {
        
    }
}