<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 27/09/2016
 * Time: 17:58
 */

namespace ClinicLE\Entity\Value;


abstract class AbstractValue
{
    private $_validationIssues=array();
    protected $_rawValue;
    protected $_field;

    /**
     * @return ADD_Field_AbstractField
     */
    public function getField()
    {
        return $this->_field;
    }
    function __construct($field) {
        $this->_field = $field;
    }

    abstract function asDisplay();
    abstract function asDatabaseFormat();
    function getRawValue() {
        return $this->_rawValue;
    }
    abstract function setFromDatabase($databaseValue);
    function setFromSubmit($submitValue) {
        $this->_rawValue = $submitValue;
    }
    function getDataType() {
        return "text";
    }
    function isSerialized() {

    }
    function getDataLength() {
        return null;
    }

    function setRawValue($rawValue) {
        return $this->_rawValue = $rawValue;
    }

    function populateFromDatabase($values, $useDefault) {
        $fieldName = $this->getField()->getName();
        if ($useDefault) {
            return $this->populateInitialValue();
        }
        if(isset($values[$fieldName])) {
            $this->setFromDatabase($values[$fieldName]);
        }
    }
    function populateInitialValue() {
        $sys = CLE::sys();
        $eval = $sys->getService(\Clinicle\Form\Service\FormServices::FIELD_EVALUATOR)->evaluate($this->getField()->getSetting('default'));
        $this->setRawValue($eval);
        return;
    }
    function populateFromSubmit($values, $useDefault) {
        $fieldName = $this->getField()->getName();
        if ($useDefault) {
            $this->populateInitialValue();
        }
        if(isset($values[$fieldName])) {
            $this->setFromSubmit($values[$fieldName]);
        }
    }

    function updateRawValue($newValue) {
        if ($newValue==$this->getValue()) return false;
        $this->setValue($newValue);
        return true;
    }
    function clearValue() {
        $this->setValue(null);
    }

    function removeAll($newValue) {
        $oldValue = $this->getValue();
        if (Util::isEmpty($oldValue)) return false;
        $this->setValue(null);
        return true;
    }

    function isEqual($testValue) {
        return $this->getRawValue()==$testValue;
    }
    function valueEqual($testValues) {
        if (in_array($this->getValue(),$testValues)) return true;
    }

    function addValidationIssue($issue) {
        $this->_validationIssues[]=$issue;
    }
    function getValidationIssues() {
        return $this->_validationIssues;
    }

    function isValid() {
        return true;
    }

    function isEmpty() {
        return Util::isEmpty($this->_rawValue);
    }

}