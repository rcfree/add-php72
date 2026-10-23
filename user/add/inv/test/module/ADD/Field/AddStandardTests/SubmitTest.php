<?php

class ADD_Field_AddStandardTests_SubmitTest extends ADD_Test_EncounterBase {
    protected $_fieldName=null;
    public function setUp() {
        parent::setUp();
        $this->givenDictionaryGroupsReturned(3);
        
        
	//ADD::db('dictionary')->expects($this->any())->method("hasDictionary")->will($this->returnValue(true));
			    	    
        $this->_fieldName = getenv('ADD_FIELD_TEST');
        $this->_page->setupComponents();
    }
    function testItemsAreSubmittedAsExpected() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $page->setPermission('edit',true);
        $page->setupComponents();
        ADD::log()->info("fieldName:".$this->_fieldName);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        $data = array();
        for($i=0;$i<=7;$i++) {
            $data[]=array("name"=>"group$i","title"=>"Group ".$i, "value"=>$i);
        }
        
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsInGroups")->will($this->returnValue($data));
          	    
        $cer->expects($this->exactly(8))->method("store");
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $field = $page->getComponent('Form')->getField($this->_fieldName);
        
        $obsMaps = $field->getSetting('obs_map');
        $values = array($this->_fieldName=>"submit");
        if ($obsMaps) {
            foreach($obsMaps as $obsMap) {
                    list($source,$target) = explode(":",$obsMap);
                    $values[$source]="";
            }
        }
        $page->submitForm($values);
        $this->assertHasMessages(array($page->getSetting('title')." saved",$field->assertSetting('add_message'). " (8 records)"));
    }
    
}
