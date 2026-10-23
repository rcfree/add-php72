<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 18/04/2014
 * Time: 10:00
 */

interface ADD_Field_OptionInterface {
    function initOptions($field,$page);
    function getOptionList();
    function getConceptList();
    function getConceptWithValue($value);
    function getLabelWithValue($value);
    function getValueWithLabel($label);
    function hasOptionWithLabel($label);
    function hasOptionWithValue($value);
    function countOptions();
    function hasNoOptions();
    function findItemsMatchingTerm($value);
    function setDictionaryName($dictName);
    function isInitAfterPopulate();
	function isInit();
} 
