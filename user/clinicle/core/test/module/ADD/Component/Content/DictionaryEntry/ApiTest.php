<?php

class ADD_Component_Content_DictionaryEntry_ApiTest extends ADD_Test_EncounterBase {
	private $_med = null;	
	function setUp() {
		parent::setUp();
		ADD::web()->setContentClass('Json');
               
                $this->_apage = ADD::sys()->getModule('api')->getPage('dictitem');
                //$this->loadTestConfig();
	}
	function testDictionaryCreateIfPermission() {
		$add = ADD::sys();
               
                $page = $this->_page;
		$add->setParams(array('dict_name'=>$page->assertSetting('DictionaryEntry.dictionary'),'action'=>'create'));
		
		$page = $this->_page;
		$page->setPermission('create',true);
		
		$content = $this->_apage->createOutput(array());
		$expValues=array('data'=>array('group'=>'','value'=>'','title'=>'','group_label'=>'','new_group'=>0,'__action'=>'create'));
		$this->assertEquals(json_encode($expValues),$content->output());
	}
	
	function testDictionaryUpdateWithNoGroup() {
		$add = ADD::sys();
		$page = $this->_page;
                ADD::db('dictionary')->expects($this->once())->method("getDictionaryItem")->will($this->returnValue(
                        array("id"=>5,"name"=>"rat","title"=>"Rat","value"=>99)
                ));
		$add->setParams(array('id'=>5,'dict_name'=>$page->assertSetting('DictionaryEntry.dictionary'),'action'=>'edit'));
		
		$page->setPermission('edit',true);
		
		$expValues = array("id"=>5,"name"=>"rat","title"=>"Rat","value"=>99,"group"=>'');
		$expValues['__id']=5;
		$expValues['__action']='edit';
		$expValues['new_group']=0;
		$content = $this->_apage->createOutput(array());
		$this->assertEquals(json_encode(array("data"=>$expValues)),$content->output());
	}
        
        function testDictionaryUpdateExistingGroup() {
		$add = ADD::sys();
		$page = $this->_page;
                ADD::db('dictionary')->expects($this->once())->method("getDictionaryItem")->will($this->returnValue(
                        array("id"=>5,"name"=>"rat","title"=>"Rat","value"=>99,"dictionary_group_id"=>88)
                ));
                ADD::db('dictionary')->expects($this->once())->method("getDictionaryGroup")->will($this->returnValue(
                        array("id"=>88,"name"=>"rodents","title"=>"Rodents")
                ));
		$add->setParams(array('id'=>5,'dict_name'=>$page->assertSetting('DictionaryEntry.dictionary'),'action'=>'edit'));
		
		$page->setPermission('edit',true);
		
                $expValues = array("id"=>5,"name"=>"rat","title"=>"Rat","value"=>99,"dictionary_group_id"=>88,"group"=>'rodents');
                $expValues['__id']=5;
		$expValues['__action']='edit';
                $expValues['new_group']=0;
                $content = $this->_apage->createOutput(array());
                $this->assertEquals(json_encode(array("data"=>$expValues)),$content->output());
	}
        
        function testDictionaryDeleteExistingItem() {
		$add = ADD::sys();
		$page = $this->_page;
                ADD::db('dictionary')->expects($this->once())->method("getDictionaryItem")->will($this->returnValue(
                        array("id"=>5,"name"=>"rat","title"=>"Rat","value"=>99,"dictionary_group_id"=>88)
                ));
                
		$add->setParams(array('id'=>5,'dict_name'=>$page->assertSetting('DictionaryEntry.dictionary'),'action'=>'delete'));
		
		$page->setPermission('delete',true);
		
                $expValues = array('__action'=>'delete',"new_group"=>0);
                $content = $this->_apage->createOutput(array());
                $this->assertEquals(json_encode(array("data"=>$expValues)),$content->output());
	}
        
	/*
	function testEncounterViewIfPermission() {
		$add = ADD::sys();
		$page = $this->_page;
		$add->setParams(array('id'=>1,'fid'=>$page->getName(),'lid'=>$this->_module->getName(),'action'=>'view'));
		$em = $add->getEncounterManager();
		$er = $this->getERMock();
		$er->expects($this->once())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getEncData")->will($this->returnValue(array()));
		$er->expects($this->once())->method("getObsValues")->will($this->returnValue($this->_conf->getSectionSettings('FormSubmitWithExampleData')));
		
		$em->setExpected($page,"record",$er);
		$page->setPermission('view',true);
		
                $expValues = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
                $expValues['__id']=1;
		$expValues['__action']='view';
                
		$this->_apage->createOutput();
                $this->assertEquals(json_encode($expValues),ADD::web()->getContent()->output());
                
	}
	/*
	function testEncounterDeleteIfPermission() {
		$add = ADD::sys();
                $page = $this->_page;
		$add->setParams(array('id'=>2,'fid'=>$page->getName(),'lid'=>$this->_module->getName(),'action'=>'delete'));
		$em = $add->getEncounterManager();
		$er = $this->getERMock();
		$er->expects($this->once())->method("exists")->will($this->returnValue(true));
		$er->expects($this->never())->method("getEncData");
		$er->expects($this->never())->method("getObsValues");
		$er->expects($this->never())->method("store");
		
		$em->setExpected($page,"record",$er);
		$page->setPermission('delete',true);
		$this->_apage->createOutput();
	}
         * 
         */
}