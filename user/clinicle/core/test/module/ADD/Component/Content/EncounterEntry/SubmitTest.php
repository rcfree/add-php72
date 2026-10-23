<?php

$home = getenv("ADD_PATH");
set_include_path(get_include_path() . ":$home/test/module");


class ADD_Component_Content_EncounterEntry_SubmitTest extends ADD_Test_EncounterBase {
	public static $_expConfig = null;
	public static $dataSubmitted = false;
    protected $_conf = null;
	
	
    public function setUp() {
        parent::setUp();
		ADD::sys()->setState(ADD_System::EVAL_EXCEPTION,true);
        $this->loadTestConfig();
    }

    /**
     * @expectedException ADD_NoPermissionToSubmitException
     * @expectedExceptionMessage Sorry you do not have permission to edit
     */
    public function testFormSubmitFailsIfRecordAndOnlyCreatePermission() {
        $page = $this->_page;
        $em = ADD::sys()->getEncounterManager();

        ADD::sys()->setParam('t', 'TOKEN');
        $this->givenMockPatientIsSelected();
        ADD::db('patient')->expects($this->any())->method("doesPatientExist")->will($this->returnValue(true));
        $cer = $this->getMock('ADD_EncounterRecord');
        $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) {
                    return array("name" => $name, "title" => ucfirst(str_replace("_", " ", $name)));
                }));

        $cer->expects($this->any())->method("exists")->will($this->returnValue(true));
        $cer->expects($this->never())->method("store");
        $em->setExpected($page->getSetting('encounter'), "child", $cer);

        $page->setPermission('create', true);

        ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) {
                    return array("1" => $name . "1", "2" => $name . "2", "3" => $name . "3");
                }));

        $page->setupComponents();
        $page->submitForm(array());
        $this->assertHasNoWarnings();
    }

    /**
     * @expectedException ADD_NoPermissionToSubmitException
     * @expectedExceptionMessage Sorry you do not have permission to create
     */
    public function testFormSubmitFailsIfNoRecordAndNoCreatePermission() {
        $page = $this->_page;
        ADD::sys()->setParam('t', 'TOKEN');
        $em = ADD::sys()->getEncounterManager();

        $this->givenMockPatientIsSelected();
        $cer = $this->getERMock();
        $cer->expects($this->any())->method("exists")->will($this->returnValue(false));
        $cer->expects($this->never())->method("store");
        $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) {
                    return array("name" => $name, "title" => ucfirst(str_replace("_", " ", $name)));
                }));

        $page->setupComponents();
        $page->submitForm(array());
    }

    public function testFormSubmitWithExampleData() {
        //if (!$this->_conf)
            //$this->markTestSkipped("Test config file (" . $this->_module->getPath() . "/test/config/" . $this->_page->getName() . ".ini) missing");
        ADD::sys()->setState(ADD_System::NO_UNCLEAN_WARNING,true);
        $page = $this->_page;
		foreach($page->getModule()->getPages() as $mpage) {
			$mpage->setPermissions(array("create"=>true,"edit"=>true,"view"=>true));
		}
		$page->getComponent('Form')->beforeInstall();
        ADD::sys()->setParam('t', 'TOKEN');
        $this->givenMockPatientIsSelected();
        $this->givenTokenDoesNotExist();
        $em = ADD::sys()->getEncounterManager();
        $cer = $this->getERMock();
        $cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) {
                    return array("name" => $name, "title" => ucfirst(str_replace("_", " ", $name)));
                }));

        $cer->expects($this->any())->method("exists")->will($this->returnValue(false));

        ADD::db('patient')->expects($this->atLeastOnce())->method("doesPatientExist")->will($this->returnValue(true));
        ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) {
                    return array("1" => $name . "1", "2" => $name . "2", "3" => $name . "3");
                }));

        $page->setPermission('create', true);
        $page->setupComponents();
        $expCount = $page->hasComponent('Locking') ? $this->exactly(2) : $this->once();
        self::$_expConfig = $this->_conf->getSection('FormSubmitWithExampleData')->getConfig();
        if(count(self::$_expConfig)==0) throw new Exception("No fields provided in FormSubmitWithExampleData in config file");
        
        $cer->expects($expCount)->method("store")->with($this->callback(function($args) {
           
                    if (ADD_Util::isEmpty($args) && ADD_Component_Content_EncounterEntry_SubmitTest::$dataSubmitted)
                        return true;

                    if (ADD_Component_Content_EncounterEntry_SubmitTest::$_expConfig == $args) {
                        ADD_Component_Content_EncounterEntry_SubmitTest::$dataSubmitted = true;
                        return true;
                    }
                    ADD::log()->error("Arrays do not match:\nExpected:" .json_encode(ADD_Component_Content_EncounterEntry_SubmitTest::$_expConfig) . "\nObserved:" . json_encode($args));
                    //$diffKeys = array_diff_assoc($args,ADD_Component_Content_EncounterEntry_SubmitTest::$_expConfig);
                    //ADD::log()->error("Difference:".print_r($diffKeys,1));
                    
                    }));
        $em->setExpected($page->getSetting('encounter'), "child", $cer);

        $page->submitForm(self::$_expConfig);
        $this->assertHasNoWarnings();
    }

}

?>	