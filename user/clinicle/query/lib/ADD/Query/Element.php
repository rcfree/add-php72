<?php
abstract class ADD_Query_Element {
    protected $_attrs=array();
    function __construct($attrs = array()) {
        $this->_attrs = new ADD_Config($attrs,true);
    }
    function getValues() {
        return $this->getAttrs('values');
    }
    function getAttrs() {
        return $this->_attrs->getSettings();
    }
    function getAttr($attrName,$default=null) {
        return $this->_attrs->getSetting($attrName,$default);
    }
    function assertAttr($attrName) {
        return $this->_attrs->assertSetting($attrName);
    }
    
}