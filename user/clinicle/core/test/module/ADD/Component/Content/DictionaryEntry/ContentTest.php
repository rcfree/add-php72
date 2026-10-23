<?php

class ADD_Component_Content_DictionaryEntry_ContentTest extends ADD_Test_EncounterBase {
	
public function setUp() {
    parent::setUp();
    ADD::db('dictionary')->expects($this->any())->method("hasDictionary")->will($this->returnValue(true));
    ADD::db('dictionary')->expects($this->any())->method("getDictionaryGroups")->will($this->returnValue(array(
        array("id"=>5,"name"=>"group1","title"=>"Group 1"),
        array("id"=>6,"name"=>"group2","title"=>"Group 2"),
        array("id"=>7,"name"=>"group3","title"=>"Group 3")
     )));
           
    ADD::db('dictionary')->expects($this->any())->method("getDictionaryByName")->will($this->returnValue(array("id"=>1,"name"=>"dictionary")));	    	    
    
    $dictName = $this->_page->getSetting('DictionaryEntry.dictionary');
    $digMap = array(
        array($dictName,null,
            array(array('id'=>100,'value'=>1,'title'=>'item1'),array('id'=>101,'value'=>2,'title'=>'item2'),array('id'=>102,'value'=>3,'title'=>'item3'))
        ),
        array($dictName,"group1",
            array(array('id'=>100,'value'=>1,'title'=>'item1'),array('id'=>101,'value'=>2,'title'=>'item2'),array('id'=>102,'value'=>3,'title'=>'item3'))
        ),
        array($dictName,"group2",
            array(array('id'=>100,'value'=>1,'title'=>'item1'),array('id'=>101,'value'=>2,'title'=>'item2'),array('id'=>102,'value'=>3,'title'=>'item3'))
        ),
        array($dictName,"group3",
            array(array('id'=>100,'value'=>1,'title'=>'item1'),array('id'=>101,'value'=>2,'title'=>'item2'),array('id'=>102,'value'=>3,'title'=>'item3'))
        )
    );
    ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsInGroup")->will($this->returnValueMap($digMap));
    
    }
public function testFormTitleIsCorrect() {
    $this->assertEquals($this->_page->getSetting('title'),$this->_page->createTitle());
}
public function testFormDisplayedIfViewPermission() {
        $page = $this->_page;
        $this->givenMockPatientIsSelected();
        $page->setPermission('view',true);
        $page->setupComponents();
        $page->createContent();
        $this->assertGreaterThan(0,strlen(ADD::web()->getContent()->output()));
        //$this->assertHasNoWarnings();
        
        
 }
 
 public function testFormDisplayedFromPageUrl() {
        $currPage = $this->_page;
        $url = $currPage->createMenuPath();
        ADD::log()->error("url:$url");
        
        $this->givenMockPatientIsSelected();
        
        $page = ADD::sys()->getAccessManager()->getCurrentPage($url);
        $page->setPermission('view',true);
        $page->setupComponents();
        $page->createContent();
        $this->assertGreaterThan(0,strlen(ADD::web()->getContent()->output()));
        //$this->assertHasNoWarnings();
        //ADD::log()->error("web:".print_r(ADD::web()->getContent(),1));
        
        
 }
 
 /**
  * @expectedException ADD_NoPermissionToAccessException
  * @expectedExceptionMessage You do not have permission to view this page
  */
 public function testFormNotDisplayedIfNoPermission() {
        $page = $this->_page;

        $this->givenMockPatientIsSelected();
        $page->setupComponents();
        $page->createContent();
 }
                 

}
?>	