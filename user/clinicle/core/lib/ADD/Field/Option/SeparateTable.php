<?php

class ADD_Field_Option_SeparateTable extends ADD_Field_Option_Standard {
    protected $_optionList = array();
	private $_dictFilter = null;
	private $_dictFilterValue = null;
	private $_dictOrder;
	
    function initOptions($field,$page) {
        $this->setDictionaryName($field->getSetting('dictionary'));
		$this->_dictFilter = $field->getSetting('dictionary_filter');
		$this->_dictFilterValue = $field->getSetting('dictionary_value');
		$this->_dictValueField = $field->getSetting('dictionary_value_field','value');
		$this->_dictOrder = $field->getSetting('dictionary_order');
    }
	function getOptionList() {
		if($this->_optionList) {
			return $this->_optionList;
		}
		$sql = "select * from `".$this->_dictName."` where (is_disabled!=1 or is_deleted!=1)";
		$params = array();
		if ($this->_dictFilter) {
			$sql.=" and ".$this->_dictFilter." = ?";
			$params[]=$this->_dictFilterValue;
		}
		if($this->_dictOrder) {
			$sql.=" order by ".$this->_dictOrder;
		}
		$items = ADD::db('dictionary')->fetchAll($sql,$params);
	
		$options = array();
		foreach($items as $item) {
			$options[$item[$this->_dictValueField]]=$item['title'];
		}
		$this->_optionList = $options;
		return $options;
	}
	function isInitAfterPopulate() {
		return false;
	}
	function hasNoOptions() {
		if ($this->countOptions()<=1) return true;
		return false;
	}
    function findItemsMatchingTerm($term,$startsWith=false) {
        return ADD::db('dictionary')->getSeparateDictionaryItemsMatchingTerm($this->_dictName,$term,$startsWith);
    }
    
    function findLabelWithValue($value) {
    	$item = ADD::db('dictionary')->fetchFirst("select * from `".$this->_dictName."` where value=?",array($value));
        return $item ? $item['title'] : null;
    } 
   function getDataWithValue($value) {
   	return ADD_Util::val($value,$this->_optionList);
   	}
}