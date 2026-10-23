<?php


class ADD_Page_Api_QualifierItems extends ADD_Page_Api {
	function createMenuPath() {
       return "api/qualifieritems";
    }
	
	function apiOutput() {
		$add =  ADD::sys();
		$code = $add->getParam('code');

		$db = ADD::db('BASE');
		//$dictName = "comorb-groups";
		
		//$sql = "select value from dictionary_item di left join dictionary d on di.dictionary_id = d.id where d.name=? and (di.data like ? or di.data like ?)";
		
		//$params = array($dictName,"%".$code.";%","%".$code.'\"%');
		//$matchingGroup = $ddb->fetchSingleValue($sql,$params);
		//if(!$matchingGroup) return null;
		$groupItems = $db->fetchAll("SELECT q.* FROM icd10_qualifier q
left join icd10_group_code gc on gc.icd10_group_id = q.icd10_group_id
left join icd10_code c on gc.icd10_code_id = c.id
where c.value=?",array($code));
		return $groupItems;
	}
}
?>