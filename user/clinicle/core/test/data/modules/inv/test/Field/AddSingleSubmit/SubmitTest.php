<?php
set_include_path(getenv('ADD_PATH')."/test/module");

require_once("Component/EncounterBase.php");

class Field_AddSingleSubmit_SubmitTest extends Component_EncounterBase {
    protected $_fieldName=null;
    public function setUp() {
        parent::setUp();
        $this->givenDictionaryGroupsReturned(3);
        $this->_fieldName = getenv('ADD_FIELD_TEST');
        $this->_page->setupComponents();
    }
    function testDealsWithPreExistingWhenNoTitle() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $this->givenDictionaryGroupsReturned(2);
        $page->setPermission('edit',true);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemByTitle")->will($this->returnCallback(function($title) { return array("value"=>"4","name"=>strtolower($title),"title"=>$title); }));
          	    
        $cer->expects($this->once())->method("store");
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $data = array(
            $this->_fieldName=>"submit",
            "single_test"=>4,
            "__token"=>"TOKEN",
            "__single_test_ac"=>"Rabbit"
           );
        $page->submitForm($data);
        $this->assertHasMessages(array($page->getSetting('title')." saved","Successfully added 'Rabbit' ".$page->getSetting('single')));
        $this->assertHasNoWarnings();
    }
    
    function testDealsWithPreExistingWhenTitle() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $page->setPermission('edit',true);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemByTitle")->will($this->returnCallback(function($dictName,$title) { return array("value"=>"4","name"=>strtolower($title),"title"=>$title); }));
          	    
        $cer->expects($this->once())->method("store");
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $data = array(
            $this->_fieldName=>"submit",
            "single_test"=>"",
            "__single_test_ac"=>"Cat",
            "__token"=>"TOKEN"
           );
        $page->submitForm($data);
        $this->assertHasMessages(array($page->getSetting('title')." saved","Successfully added 'Cat' ".$page->getSetting('single')));
        $this->assertHasWarning(ucfirst($page->getSetting('test_single'))." with name 'Cat' already exists. Not created");
    }
    
    function testCreatesNewWithDoesNotExist() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $page->setPermission('edit',true);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemByTitle")->will($this->returnValue(null));
          	    
        $cer->expects($this->once())->method("store");
        $em = ADD::sys()->getEncounterManager();
        $em->setExpected($page->getParent(),"child",$er);
        $em->setExpected($page,"newChild",$cer);
        $data = array(
            $this->_fieldName=>"submit",
            "single_test"=>"",
            "__single_test_ac"=>"Rabbit",
            "__token"=>"TOKEN"
           );
        $field = $page->getComponent('Form')->getField($this->_fieldName);
        $obsMaps = $field->getSetting('obs_map');
       
        if ($obsMaps) {
            foreach($obsMaps as $obsMap) {
                    list($source,$target) = explode(":",$obsMap);
                    $data[$source]="";
            }
        }
        
        $page->submitForm($data);
        $this->assertHasMessages(array($page->getSetting('title')." saved","Created new ".$page->getSetting('test_single')." 'Rabbit'","Successfully added 'Rabbit' ".$page->getSetting('single')));
        $this->assertHasNoWarnings();
    }
    /**
     * @expectedException ADD_AccessException
     * @expectedExceptionMessage Sorry you do not have permission to create
     */
    function testFailsToCreateIfNoCreatePermission() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        //$page->setPermission('edit',true);
        $er = $this->getERMock();
        $er->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer = $this->getERMock();
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemByTitle")->will($this->returnValue(null));
          	    
        
        $page->submitForm(array());
        $this->assertHasMessages(array($page->getSetting('title')." saved","Created new ".$page->getSetting('test_single')." 'Rabbit'","Successfully added ".$page->getSetting('single')));
        $this->assertHasNoWarnings();
    }
    
}
