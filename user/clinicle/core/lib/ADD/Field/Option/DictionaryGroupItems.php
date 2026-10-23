<?php

class ADD_Field_Option_DictionaryGroupItems extends ADD_Field_Option_Standard {
    private $_dict = null;
    private $_dictGroup;
    const LOOKUP="LookupDictGroupItems";
    private $disabledOptions=array();
    function isInitAfterPopulate() {
        return true;
    }
    function initOptions($field,$page) {
        $this->_dictName = $field->assertSetting('dictionary');
        $this->_dictGroup = $field->getSetting("dictionary_group");
        $dictName = $this->getDictionaryName($page);
        $groupValue = $this->getDictionaryGroupName($page);
        if(!$groupValue) {
            return;
        }
        $items = ADD::db('dictionary')->getDictionaryItemsInGroup($dictName,$groupValue);
             
        if (ADD_Util::isEmpty($items)) throw new ADD_FieldException($field->getType()." field ''".$field->getName()."' has no dictionary or group options, or the dictionary '".$dictName."' does not exist");
        $hideDisabled = $field->getSetting('hide_disabled');
        foreach($items as $item) {
            $title = $item['title'];
            if($hideDisabled && $item['is_disabled']==1) {
                $title="($title)";
                $this->disabledOptions[]=$item['value'];
            }
            $this->_optionList[$item['value']]=$title;
        }
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
    function getDictionaryGroupName($page) {
        if ($this->_dictGroup) {
            return $this->_dictGroup;
        }
        $lupField = $page->getComponent('Form')->getField($page->assertSetting(self::LOOKUP.'.lookup_field'));
        if(!$lupField) throw new ADD_FieldDoesNotExistException("Field '".$page->getSetting(self::LOOKUP.'.lookup_field')."' does not exist");
        if(!$lupField->getValue()) return;
    }
    function getDictionaryName($page) {
        if($this->_dictName) {
            return $this->_dictName;
        }
        return $page->assertSetting(self::LOOKUP.'.dictionary');
    }
    function isOptionDisabled($value) {
        if(in_array($value,$this->disabledOptions)) return true;
        return false;
    }

}