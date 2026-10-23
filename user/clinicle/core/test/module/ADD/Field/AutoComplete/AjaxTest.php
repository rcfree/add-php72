<?php

$home = getenv("ADD_PATH");
set_include_path("$home/test/module");


class ADD_Field_AutoComplete_AjaxTest extends ADD_Test_EncounterBase {
    function setUp() {
        parent::setUp();
        ADD::web()->setContentClass('Json');
        
                
        $this->_apage = ADD::sys()->getModule('api')->getPage('dict');
    }
    function testAutocompleteWhenTerms() {
        $add = ADD::sys();
        $add->setParam('term','dog');
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsMatchingTerm")->will($this->returnCallback(function($name,$term) { 
            return array(array("title"=>ucfirst($term)."ie","value"=>432)); 
        }));
        $content = $this->_apage->createOutput(array());
        $this->assertEquals('{"data":[{"title":"Dogie","value":432,"html":"<b>Dog<\/b>ie","data":{"title":"Dogie","value":432}}]}',$content->output());
    }
    
    function testAutocompleteWhenNoTerms() {
        $add = ADD::sys();
        $add->setParam('term','dog');
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsMatchingTerm")->will($this->returnCallback(function($name,$term) { 
            return array(); 
        }));
        $content = $this->_apage->createOutput(array());
        $this->assertEquals('{"data":[{"value":null,"html":"No items match terms","message":true}]}',$content->output());
    }
}
