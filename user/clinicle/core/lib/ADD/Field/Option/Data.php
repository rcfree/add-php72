<?php

class ADD_Field_Option_Data extends ADD_Field_Option_Standard {
    function initOptions($field,$page) {
        $options = ADD_Util::getCsvValues($field->getFieldData('Options'));
        $this->_optionList=$this->_optionList+$options;
        $this->_conceptList=$field->getFieldData('Concept');
        parent::initOptions($field,$page);
    }
}