<?php


class ADD_Field_ConditionPushButtons extends ADD_Field_PushButtons {
	private $_comorbList;
	private $_qualifier = false;
	function finaliseField($page) {
		$this->_group = ADD::sys()->getParam('group');
		$this->_parentId = ADD::sys()->getParam('eid');
			
		if(!$this->_group) return parent::finaliseField($page);
		
		$this->setSetting('dictionary_value',$this->_group);
		
		$parentRecord = $page->getParentPage()->getEncounterRecord($this->_parentId);
		$this->_comorbList = new ADD_Tool_ComorbList($parentRecord);
		$this->_comorbList->initExistingConditions();
		parent::finaliseField($page);
	}
	function getOnClick($optionValue,$page) {
		$values = array("icd10_code"=>$optionValue);
		$json = json_encode($values, JSON_HEX_APOS | JSON_HEX_QUOT);
		$id = $this->_comorbList->getRecordIdForCode($optionValue);
		
		if($this->_qualifier && !$this->_comorbList->isConditionSelected($optionValue)) {
			return "CodeBreaker.requestQualifier(this,".($id ? $id : "null").",".htmlentities($json).");";
		}
		return "ADD.call('".$this->getName()."','toggle',[this,".($id ? $id : "null").",".htmlentities($json).",'comorb_results_grid'])";
	}
	function getClassName($optionValue, $page) {
		if($this->_comorbList->isConditionPrimary($optionValue)) {
			return "btn btn-danger";
		}
		if($this->_comorbList->isConditionSelected($optionValue)) {
			return "btn btn-success";
		}
		return "btn";
	}
	
}
?>