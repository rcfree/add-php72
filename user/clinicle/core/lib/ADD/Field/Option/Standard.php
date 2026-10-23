<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 18/04/2014
 * Time: 10:54
 */

abstract class ADD_Field_Option_Standard implements ADD_Field_OptionInterface {
    protected $_optionList = array();
    protected $_conceptList = array();
    protected $_dictName = null;
    protected $_init = false;
    function getOptionList()
    {
        return $this->_optionList;
    }
	function isInit() {
		return $this->_init;
	}
    function getConceptList() {
        return $this->_conceptList;
    }

    function getLabelWithValue($value)
    {
        return ADD_Util::val($value,$this->_optionList);
    }

    function getValueWithLabel($label)
    {
        return array_search($label,$this->_optionList);
    }

    function getConceptWithValue($value) {
        return ADD_Util::val($value,$this->_conceptList);
    }

    function hasOptionWithLabel($label)
    {
        return array_search($label,$this->_optionList) === false ? false : true;
    }

    function hasOptionWithValue($value)
    {
        return array_key_exists($value,$this->_optionList);
    }

    function initOptions($field,$page)
    {
        if ($this->hasNoOptions()) {
			$ex = new Exception();
            if (!$field->getSetting('empty')) ADD::web()->addWarning($field->getType()." field has no options (field '".$field->getName()."')".$ex->getTraceAsString());
            $field->setFinalised(true);
        }
		$this->_init=true;
    }
    function prependOption($value,$label) {
        $this->_optionList = array($value=>$label) + $this->_optionList;
    }
    function countOptions()
    {
        return count($this->_optionList);
    }

    function hasNoOptions()
    {
       return $this->countOptions()==0;
    }
    
    function setOptionList($optionList) {
        $this->_optionList = $optionList;
    }
    
    function findLabelWithValue($value) {
        return $this->getLabelWithValue($value);
    }
    
    function setDictionaryName($dictName) {
        $this->_dictName = $dictName;
    }
    
    function findItemsMatchingTerm($value) {
        return array();
    }
    function isInitAfterPopulate() {
        return false;
    }
	function isOptionDisabled($value) {
		return false;
	}

}