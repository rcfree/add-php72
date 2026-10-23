<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 27/09/2016
 * Time: 18:00
 */

namespace ClinicLE\Entity\Value;

class StringValue extends AbstractValue {

    function setFromDatabase($databaseValue) {
        $this->setRawValue($databaseValue);
    }
    function getDataType() {
        return "varchar";
    }
    function isSerialized() {
        return false;
    }
    function getDataLength() {
        return $this->getField()->getSetting('size',30);
    }
    function asDisplay() {
        return $this->getRawValue();
    }
    function asDatabaseFormat() {
        return $this->getRawValue();
    }
    function isEqualTo($value) {
        return (string)$value===(string)$this->asDisplay();
    }

}