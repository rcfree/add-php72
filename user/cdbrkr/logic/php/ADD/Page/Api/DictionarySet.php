<?php


class ADD_Page_Api_DictionarySet extends ADD_Page_Api {
	function createMenuPath() {
       return "api/dictionaryset";
    }
	
	private $_comorbList;
	private $_optionList = array();
	
	function apiOutput() {
		$add =  ADD::sys();
		$this->_group = $add->getParam('group');
		if(!$this->_group) return;
		$this->_parentId = $add->assertParam('parentid');
		$this->setSetting('dictionary_value',$this->_group);
		$checkSelectPage = $add->getParam('check_select_page');
		$checkEncType = null;
		if($checkSelectPage) {
			$checkEncType = $add->getPageFromPim($checkSelectPage)->getSetting('encounter');
		}
		$parentRecord = $add->getEncounterManager()->sectionById($this->_parentId);
		$parentRecord->retrieve();
		$this->_comorbList = new ADD_Tool_ComorbList($parentRecord,$checkEncType);
		$this->_comorbList->initExistingConditions();
		
		$groupDictName = $add->assertParam("groupdict");
		
		$lookupDictName = $add->assertParam('lookupdict');
		$this->populateDictionaryItemsUsingGroupValues($this->_group);
		$grid = array();
		$gridColumns = $add->getParam('columns',3);
		$currGridColumns=0;
		
		$row = array();
		foreach($this->_optionList as $code => $item) {
			if($currGridColumns>=$gridColumns) {
				$grid[]=$row;
				$currGridColumns=0;
				$row = array();
			}
			$row[] = $this->createSetItem($code,$item);
			
			$currGridColumns++;
		}
		if(count($row)>0) {
			$grid[]=$row;
		}
		return $grid;
	}
	function createSetItem($value,$item) {
		return array(
				"code"=>$value,
				"description"=>$item['title'], 
				"is_primary"=>$this->_comorbList->isConditionPrimary($value),
				"is_selected"=>$this->_comorbList->isConditionSelected($value),
				"selected_id"=>$this->_comorbList->getRecordIdForCode($value)
			);
	}
	function populateDictionaryItemsUsingGroupValues($dictItemValue) {
        $ddb = ADD::db('dictionary');
		
		$sql = "SELECT c.* FROM icd10_code c
left join icd10_group_code gc on gc.icd10_code_id = c.id
left join icd10_group g on gc.icd10_group_id = g.id
where gc.icd10_group_id=?";
		$items = $ddb->fetchAll($sql,array($dictItemValue));
		if(!$items) return;
        
        foreach($items as $item) {
        	$value = $item['value'];
        	$this->_optionList[$value]=$item;
        }
    }
	
}
?>
