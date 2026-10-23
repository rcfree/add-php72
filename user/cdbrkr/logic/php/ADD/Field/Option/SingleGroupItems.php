<?php

class ADD_Field_Option_SingleGroupItems extends ADD_Field_Option_Standard {
    private $_dict = null;
    private $_optionData = array();
    
    function initOptions($field,$page) {
        $dictName = $field->assertSetting('dictionary');
        $this->_dictName = $dictName;

        $items = ADD::db('dictionary')->fetchAll("select * from `".$dictName."` where icd10_group_id=?",$field->assertSetting('dictionary_group'));
		if (ADD_Util::isEmpty($items)) return;
        $excludeNoData = $field->getSetting('exclude_no_data');
        foreach($items as $item) {
            $value = $item['value'];
            $this->_optionList[$value]=$item['title'];
        }
    }
	function hasNoOptions() {
		if ($this->countOptions()<=1) return true;
		return false;
	}
    function findItemsMatchingTerm($term,$startsWith=false) {
        return ADD::db('dictionary')->getDictionaryItemsMatchingTerm($this->_dictName,$term,$startsWith);
    }
    
    function findLabelWithValue($value) {
        if (!$this->_dict) {
            $this->_dict = ADD::db('dictionary')->getDictionaryByName($this->_dictName);
        }
        $item = ADD::db('dictionary')->getDictionaryItemWithValue($this->_dict,$value);   
        return $item ? $item['title'] : null;
    } 
   function getDataWithValue($value) {
   	return ADD_Util::val($value,$this->_optionData);
   	}
}