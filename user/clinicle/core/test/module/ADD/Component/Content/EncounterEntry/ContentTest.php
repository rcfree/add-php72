<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path().":$home/test/module");


class ADD_Component_Content_EncounterEntry_ContentTest extends ADD_Test_EncounterBase {
	protected $_conf = null;
	protected $expectedERFunction="child";
	
		public function setUp() {
			parent::setUp();
                        $ser = $this->getERMock();
                        $ser->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
			$ser->expects($this->any())->method("exists")->will($this->returnValue(true));
                        $page = $this->_page;
                        foreach($page->getParent()->getChildPages() as $cpage) {
                            if ($cpage->getName()==$page->getName()) continue;
                            $this->setExpectedER($cpage->getSetting('encounter'),$this->expectedERFunction,$ser);
                        }
						ADD::sys()->setState(ADD_System::EVAL_EXCEPTION,true);
			$this->loadTestConfig();
		}
		public function testFormTitleIsCorrect() {
                    $page = $this->_page;
                    $ppage = $page->getParentPage();
                    $title = $page->getSetting('title') . " (".$ppage->getSetting('title').")";
                    $this->assertEquals($title,$page->createTitle());
                }
		public function testFormDisplayedIfCreateAndEditPermission() {
                        $page = $this->_page;
                        $em = ADD::sys()->getEncounterManager();
			
			$this->givenMockPatientIsSelected();
                        
			$cer = $this->getERMock();
                        
                        $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
			
			$cer->expects($this->any())->method("exists")->will($this->returnValue(false));
                        $em->setExpected($page->getSetting('encounter'),$this->expectedERFunction,$cer);
			$patient = array("id"=>123,"is_deleted"=>1,"first_name"=>"JAMES","last_name"=>"BROWN");
                        $page->setPermissions(array('create'=>true,'edit'=>true));
			$page->setupComponents();
                        $page->createContent();
                        
			$this->assertGreaterThan(0,strlen(ADD::web()->getContent()));
                        $exampleWarnings = $this->_conf->getSection('WarningsWithExampleData');
                        
                        if ($exampleWarnings) {
                            $this->assertHasWarnings($exampleWarnings->getConfig('warnings'));
                        }
                        else {
                            $this->assertHasNoWarnings();
                        }
                       
                    }

         public function testEditedContentContainsCorrectData() {
			 $page = $this->_page;
			$page->getComponent('Form')->beforeInstall();
        
			$em = ADD::sys()->getEncounterManager();
			ADD::sys()->setParam('t','TOKEN');
			$this->givenMockPatientIsSelected();
			$cer = $this->getMock('ADD_EncounterRecord');
                        $this->_expConfig = $this->_conf->getSection('FormSubmitWithExampleData')->getConfig();
                        if(count($this->_expConfig)==0) throw new Exception("No fields provided in FormSubmitWithExampleData in config file");
                        
                        $cer->expects($this->any())->method("exists")->will($this->returnValue(true));
			$cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
			$cer->expects($this->any())->method("getObsValues")->will($this->returnValue($this->_expConfig));
                        
                        $em->setExpected($page->getSetting('encounter'),$this->expectedERFunction,$cer);
			ADD::db('patient')->expects($this->exactly(1))->method("doesPatientExist")->will($this->returnValue(true));
                        ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3","4"=>$name); }));
                        
                        
            $page->setPermission('edit',true);
            $page->setupComponents();
            $page->createContent();
            $form = $page->getComponent('Form');
			
            $missingFields = array();
			$availFields=array();
			foreach($this->_expConfig as $name => $value) {
                if (!$form->getField($name)) {
                    $missingFields[]=$name;
                    continue;
                }
				$availFields[]=$name;
                $this->assertEquals($value,$form->getField($name)->getValue(),"Field match '$name'");
            }
            if (count($missingFields)>0) {
                throw new Exception("Expected ".ADD_Util::pluralize($missingFields,"field").": ".implode(",",$missingFields)."  but had:".implode(",",$availFields));
            }
                        
   		}
}
?>	