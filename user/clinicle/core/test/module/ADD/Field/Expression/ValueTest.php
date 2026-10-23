<?php

$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Field_Expression_ValueTest extends ADD_Test_FieldBase {
    
    function testExpressionsInConfig() {
        $fieldConf = $this->_conf->getSection("ExpectedExpression:".$this->_field->getName());
        if(!$fieldConf) $this->markTestSkipped("Expression field '".$this->_field->getName()."' has no config to test");
        
        $fieldSets = $fieldConf->getSettings();
        foreach($fieldSets['inputs'] as $name => $valueSet) {
            $result = $fieldSets['result'][$name];
            $inputs = ADD_Util::getCsvValues($valueSet);
            ADD_Field_Util::$values = &$inputs;
            $this->assertEquals($result,ADD_Field_Util::calculateExpression($this->_field));
        }
    }
    
}
