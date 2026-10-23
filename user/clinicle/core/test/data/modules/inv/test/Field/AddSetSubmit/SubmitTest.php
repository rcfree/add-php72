<?php
set_include_path(getenv('ADD_PATH')."/test/module");

require_once("Component/EncounterBase.php");

class Field_AddSetSubmit_SubmitTest extends Component_EncounterBase {
    protected $_fieldName=null;
    public function setUp() {
        parent::setUp();
        $this->givenDictionaryGroupsReturned(3);
        		    	    
        $this->_fieldName = getenv('ADD_FIELD_TEST');
        $this->_page->setupComponents();
    }
    function testGroupWithItemsAddedToPage() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $this->givenParamIsSet('t','TOKEN');
        $page->setPermission('edit',true);
        ADD::log()->info("fieldName:".$this->_fieldName);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        $data = array();
        
        for($i=0;$i<=3;$i++) {
            $data[]=array("name"=>"group$i","title"=>"Group ".$i, "value"=>$i);
        }
        
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsInGroups")->will($this->returnValue($data));
        
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $form = $page->getComponent('Form');
        $field = $form->getField($this->_fieldName);
        list($grpSource,$grpTarget) = explode(":",$field->assertSetting('obs_group_map'));
        $field = $page->getComponent('Form')->getField($this->_fieldName);
        $data = array($this->_fieldName=>"submit",$grpSource=>"test_set");
        $obsMaps = $field->getSetting('obs_map');
       
        $expected = array();
        if ($obsMaps) {
            foreach($obsMaps as $obsMap) {
                    list($source,$target) = explode(":",$obsMap);
                    $data[$source]="";
                    $expected[$target]="";
            }
        }
        for($i=0;$i<=3;$i++) {
            $expected[$grpTarget]=$i;
            $cer->expects($this->at($i))->method("store")->with($this->equalTo($expected));
        }
        $page->submitForm($data);
        $this->assertHasMessages(array($page->getSetting('title')." saved","Successfully added 'Test set' group (4 results)"));
        $this->assertHasNoWarnings();
    }
    
    function testAddingEmptyGroupGivesWarning() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $this->givenParamIsSet('t','TOKEN');
        $page->setPermission('edit',true);
        ADD::log()->info("fieldName:".$this->_fieldName);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        $data = array();
        
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsInGroups")->will($this->returnValue($data));
          	    
        $cer->expects($this->never())->method("store");
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $form = $page->getComponent('Form');
        $field = $form->getField($this->_fieldName);
        list($grpSource,$grpTarget) = explode(":",$field->assertSetting('obs_group_map'));
        $obsMaps = $field->getSetting('obs_map');
        $data = array($this->_fieldName => "submit", $grpSource => "empty_set");
        $expected = array();
        if ($obsMaps) {
            foreach($obsMaps as $obsMap) {
                    list($source,$target) = explode(":",$obsMap);
                    $data[$source]="";
                    
            }
        }        
        $page->submitForm($data);
        $this->assertHasMessages(array($page->getSetting('title')." saved"));
        $this->assertHasWarnings(array("There are no items in the 'Empty set' group"));
    }
    
}
