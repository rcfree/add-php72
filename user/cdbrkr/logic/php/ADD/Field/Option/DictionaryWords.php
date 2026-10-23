<?php

class ADD_Field_Option_DictionaryWords extends ADD_Field_Option_Standard {
    private $_dict = null;
	protected $_cache = array();
  	protected $_dictName;

    function initOptions($field,$page) {
        $db = ADD::db('dictionary');
        $dictName = $field->assertSetting('dictionary');
        $this->_dictName = $dictName;
        if($field->getSetting('empty')) return;
        
        $items = $db->getDictionaryItems($dictName);
        if (ADD_Util::isEmpty($items)) throw new ADD_FieldException($field->getType()." field ''".$field->getName()."' has no dictionary or group options, or the dictionary '".$dictName."' does not exist");
        foreach($items as $item) {
            $this->_optionList[$item['value']]=$item['title'];
        }
        
    }
	
    function findItemsMatchingTerm($term,$startsWith=false) {
        return ADD::db('dictionary')->getDictionaryItemsMatchingWords($this->_dictName,$term,$startsWith);
    }
    
    function findLabelWithValue($value) {
    	if(isset($this->_cache[$value])) return $this->_cache[$value];
        if (!$this->_dict) {
            $this->_dict = ADD::db('dictionary')->getDictionaryByName($this->_dictName);
        }
        $item = ADD::db('dictionary')->getDictionaryItemWithValue($this->_dict,$value);
		$this->_cache[$value]=$item ? $item['title'] : null;
        return $this->_cache[$value];
    } 
   
}