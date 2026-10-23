<?php

class ADD_Field_Option_ItemByGroup extends ADD_Field_Option_Standard {
    function initOptions($field,$page) {
        $db = ADD::db('dictionary');
        $groups = $db->getDictionaryGroups($field->getSetting('dictionary_grouped'));
        $options = $db->getGroupedDictionaryOptions($field->getSetting('dictionary_grouped'));
        if (!$options) throw new ADD_FieldException($field->getType()." field ''".$field->getName()."' has no dictionary or group options, or the dictionary '".$field->getSetting('dictionary_grouped')."' does not exist");
        $this->_optionList = array_merge($this->_optionList,$options);
        parent::initOptions($field,$page);
    }
}