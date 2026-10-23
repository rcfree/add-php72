<?php

class ADD_Component_ModalEntry_ApiTest extends ADD_Test_EncounterBase {
	private $_med = null;	
	function setUp() {
		parent::setUp();
		ADD::web()->setContentClass('Json');
               
		$this->_apage = ADD::sys()->getModule('api')->getPage('enc');
		
		$this->loadTestConfig();
	}
	function testEncounterCreateIfPermission() {
		$add = ADD::sys();
		$add->setParam('pid',null);
		$add->setPatientId(null);
		$page = $this->_page;
		$add->setParams(array('id'=>null,'fid'=>$page->getName(),'lid'=>$this->_module->getName(),'action'=>'create'));
		$em = $add->getEncounterManager();
		$er = $this->getERMock();
		$er->expects($this->never())->method("getObsValues");
		$er->expects($this->never())->method("getEncData");
		$er->expects($this->never())->method("exists");
		ADD::db('encounter')->expects($this->once())->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1)));
		
		$em->setExpected($page,"record",$er);
		$page = $this->_page;
		$page->setPermission('create',true);
		
		$content = $this->_apage->createOutput();
		$expValues=array("data"=>array('__action'=>'create'));
		$this->assertEquals(json_encode($expValues),$content->output());
	}
	
	function testEncounterUpdateIfPermission() {
		$add = ADD::sys();
		$page = $this->_page;
		$add->setParams(array('id'=>1,'fid'=>$page->getName(),'lid'=>$this->_module->getName(),'action'=>'edit'));
		$em = $add->getEncounterManager();
		$er = $this->getERMock();
                $er->expects($this->once())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getEncData")->will($this->returnValue(array()));
		$er->expects($this->once())->method("getObsValues")->will($this->returnValue($this->_conf->getSectionSettings('FormSubmitWithExampleData')));
		ADD::db('encounter')->expects($this->once())->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1)));
		
		$em->setExpected($page,"record",$er);
		$page->setPermission('edit',true);
		
                $expValues = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
                $expValues['__id']=1;
		$expValues['__action']='edit';
                $expValues = array("data"=>$expValues);
                $content = $this->_apage->createOutput();
                $this->assertEquals(json_encode($expValues),$content->output());
	}
	
	function testEncounterViewIfPermission() {
		$add = ADD::sys();
		$page = $this->_page;
		$add->setParams(array('id'=>1,'fid'=>$page->getName(),'lid'=>$this->_module->getName(),'action'=>'view'));
		$em = $add->getEncounterManager();
		$er = $this->getERMock();
		$er->expects($this->once())->method("exists")->will($this->returnValue(true));
		$er->expects($this->once())->method("getEncData")->will($this->returnValue(array()));
		$er->expects($this->once())->method("getObsValues")->will($this->returnValue($this->_conf->getSectionSettings('FormSubmitWithExampleData')));
		ADD::db('encounter')->expects($this->once())->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1)));
		
		$em->setExpected($page,"record",$er);
		$page->setPermission('view',true);
		
                $expValues = $this->_conf->getSectionSettings('FormSubmitWithExampleData');
                $expValues['__id']=1;
		$expValues['__action']='view';
         $expValues = array("data"=>$expValues);
		$content = $this->_apage->createOutput();
        $this->assertEquals(json_encode($expValues),$content->output());        
	}
	
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
		ADD::db('encounter')->expects($this->never())->method("getEncounterFormWithType")->will($this->returnValue(array("id"=>1)));
		
		$em->setExpected($page,"record",$er);
		$page->setPermission('delete',true);
		$this->_apage->createOutput();
	}
}