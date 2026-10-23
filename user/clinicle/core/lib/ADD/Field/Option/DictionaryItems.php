<?php

class ADD_Field_Option_DictionaryItems extends ADD_Field_Option_Standard {
    private $_dict = null;
	private $_cache = array();
	private $disabledOptions=array();
    private $field;
    private $lazyLoaded = false;
    function initOptions($field,$page) {
        $dictName = $field->assertSetting('dictionary');
        $this->_dictName = $dictName;
        if($field->getSetting('empty')) return;
        $this->field = $field;

		$this->_init=true;
    }
	function lazyLoadOptions() {
	    if($this->lazyLoaded) {
	        return;
        }
        $db = ADD::db('dictionary');
        $hideDisabled = $this->field->getSetting('hide_disabled');
        $items = $db->getDictionaryItems($this->_dictName);
        if (ADD_Util::isEmpty($items)) throw new ADD_FieldException($this->field->getType()." field ''"
            .$this->field->getName()
            ."' has no dictionary or group options, or the dictionary '".$this->_dictName."' does not exist");
        $valueOnly = $this->field->getSetting('value_only');
        foreach($items as $item) {
            $title = $valueOnly ? $item['value'] : $item['title'];
            if($hideDisabled && $item['is_disabled']==1) {
                $title="($title)";
                $this->disabledOptions[]=$item['value'];
            }
            $this->_optionList[$item['value']]=$title;
            $this->lazyLoaded = true;
        }
    }
    function findItemsMatchingTerm($term,$startsWith=false) {
        return ADD::db('dictionary')->getDictionaryItemsMatchingTerm($this->_dictName,$term,$startsWith);
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
   
   function isOptionDisabled($value) {
	   	if(in_array($value,$this->disabledOptions)) return true;
	   	return false;
   }

   function getOptionList()
   {
       $this->lazyLoadOptions();
       return parent::getOptionList();
   }
}