<?php

class ADD_Field_Option_DictionaryGroups extends ADD_Field_Option_Standard {
    function initOptions($field,$page) {
        $options = $this->_optionList;

        $db = ADD::db('dictionary');
        $groups = $db->getDictionaryGroups($field->getSetting('dictionary_groups'));
        foreach($groups as $group) {
            $options[$group['name']]=$group['title'];
        }
        $this->_optionList = $options;
        $this->_dictName = $field->getSetting('dictionary_groups');
        parent::initOptions($field,$page);
    }
    function findItemsMatchingTerm($term,$startsWith=false) {
        $dictGroups = ADD::db('dictionary')->getDictionaryGroupsMatchingTerm($this->_dictName,$term, $startsWith);
        $items = array();
        foreach($dictGroups as $dictGroup) {
            $dictGroup['value']=$dictGroup['name'];
            $items[]=$dictGroup;
        }
        return $items;
    }
    
    function findLabelWithValue($value) {
        $dictItem = ADD::db('dictionary')->getDictionaryGroupByName($value,$this->_dictName);
        return $dictItem ? $dictItem['title'] : null;
    }

}