<?php
abstract class ADD_Test_EncounterBase extends ADD_Test_ComponentBase {
	protected $_page = null;
	protected $_module = null;
    protected static $_db = null;
    protected static $_edb = null;
    protected static $_pdb = null;
    protected static $_adb = null;
    protected static $_ddb = null;
    protected static $_tdb = null;
    protected static $_hdb = null;
	protected static $_udb = null;
	protected static $_sdb = null;
    protected $_params=array();
	protected $_conf = null;
	protected $_cer = null;

	public function getERMock() {
		return $this->getMock('ADD_EncounterRecord');
	}
	public function setupAjaxER() {
		$cer = $this->getMock('ADD_EncounterRecord');
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryOptions")->will($this->returnCallback(function($name) { return array("1"=>$name."1","2"=>$name."2","3"=>$name."3"); }));

		$cer->expects($this->any())->method("getObsField")->will($this->returnCallback(function($name) { return array("name"=>$name,"title"=>ucfirst(str_replace("_"," ",$name))); }));
		return $cer;
	}

    public function loadTestConfig() {
    	$iniFile = $this->_module->getPath()."/test/config/".$this->_page->getName().".ini";
        if(!file_exists($iniFile)) throw new Exception("Test config file '$iniFile' not found");

        $conf = parse_ini_file($iniFile,true);
        foreach($conf as $section=>$data) {
                foreach($data as $key=>$value) {
                        if ($value=="[]") $conf[$section][$key]=array();
                }
        }
        $confObj = new ADD_Config($conf);
        $this->_conf = $confObj;
    }
    public function getEncounterRecordMocks() {
    	$per = $this->getMock('ADD_EncounterRecord');
			$er = $this->getMock('ADD_EncounterRecord');
			$cer = $this->getMock('ADD_EncounterRecord');

			return array($per,$er,$cer);
	}

	public $_ecounter = 0;
	public $_er = array();
    static $_dgNo = 0;

	public function givenDictionaryGroupsReturned($no) {
            self::$_dgNo=$no;
            ADD::db('dictionary')->expects($this->any())->method("getDictionaryGroups")->will($this->returnCallback(
                function($name) {
                $groups = array();
                for($i=0;$i<=ADD_Test_EncounterBase::$_dgNo;$i++) {
                    $groups[]=array("name"=>$name.$i,"title"=>"Group ".$name." $i");
                }
                return $groups;
                }));
        }

    public function setupEncounterRecord() {
            $per = $this->getMock('ADD_EncounterRecord');
            $er = $this->getMock('ADD_EncounterRecord');
            $cer = $this->getMock('ADD_EncounterRecord');

            $em = new ADD_Test_MockEncounterManager();

            $this->_ecounter=0;

            ADD::sys()->setEncounterManager($em);

            $this->_em = $em;

            return array($per,$er,$cer);
    }
    function givenTokenExists() {
            ADD::db('token')->expects($this->any())->method("register")->will($this->returnValue(false));
    }

    function givenTokenDoesNotExist() {
            ADD::db('token')->expects($this->any())->method("register")->will($this->returnValue(true));
            }

	 public function setupCreateContentDbExpects($page) {
        $cpages = $page->getSettingList('create-set',',');
        ADD::db('encounter')->expects($this->once())
                ->method("getEncountersWithPatientAndType")
                ->with($this->equalTo(123),$this->equalTo($page->getSetting('encounter')))
                ->will($this->returnValue(null));
        ADD::db('encounter')->expects($this->once())
                ->method("createEncounterWithTypeAndObservations")
                ->with($this->equalTo(123),$this->equalTo($page->getSetting('encounter')),$this->equalTo(array()))
                ->will($this->returnValue(array(array("id"=>1,"patient_id"=>123,"encounter_form_id"=>1),array())));
        if (count($cpages)==0) return;
        $this->_cencs = array_map(function($cpage) { return $this->_page->getChildPage($cpage)->getSetting('encounter'); },$cpages);
        ADD::db('encounter')->expects($this->exactly(count($cpages)))
                ->method("countChildEncountersWithType")
                ->with($this->equalTo(array("id"=>1,"patient_id"=>123,"encounter_form_id"=>1)),$this->callback(function($type) { if (in_array($type,$this->_cencs)) return true; }))
                ->will($this->returnValue(0));
        $cmap = array();
        $eid = 2;
        $efid = 2;
        foreach($this->_cencs as $cenc) {
            $cmap[]=array("id"=>1,"patient_id"=>123,"encounter_form_id"=>$efid,$cenc,array(),array(array("parent_encounter_id"=>1,"id"=>$eid,"patient_id"=>123,"encounter_form_id"=>$efid),array()));
            $eid++;
            $efid++;
        }

        ADD::db('encounter')->expects($this->exactly(count($cpages)))
                ->method("createChildEncounterWithTypeAndObservations")
                ->will($this->returnValueMap($cmap));
        ADD::db('audit')->expects($this->exactly(count($cpages) + 1))
                ->method("auditEncounter")
                ->with($this->equalTo("create"))
                ->will($this->returnValue(array("id"=>1,"record_type"=>"encounter","record_name"=>$page->getSetting('encounter'))));
    }
	function givenMockPatientIsSelected() {
        $pid = 123;
        ADD::sys()->setPatientId($pid);
        ADD::sys()->setParam('pid',$pid);
        ADD::db('patient')->expects($this->any())->method("doesPatientExist")->will($this->returnValue(true));
    }
    function givenPatientDoesNotExist() {
        ADD::db('patient')->expects($this->atLeastOnce())->method("doesPatientExist")->will($this->returnValue(false));
    }

    function givenParamIsSet($param,$value) {
        ADD::sys()->setParam($param,$value);
    }

    public function assertHasContent($content,$name="main") {
        $this->assertEquals($content,ADD::web()->getContent($name)->output());
    }
    public function assertHasTextContent($content,$name="main") {
        $this->assertEquals($content,ADD::web()->getContent($name)->toText());
    }
    public function assertContainsTextContent($content,$name="main") {
        $this->assertRegExp("/".preg_quote($content,"/")."/",ADD::web()->getContent($name)->toText());
    }
    public function assertHasWarning($warning) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $this->assertEquals(array($warning),ADD::web()->getWarnings());
    }

	public function assertHasWarnings($warnings) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        if(!is_array(ADD::web()->getWarnings())) throw new Exception("Expected warnings:".print_r($warnings,1)." not seen");
        $this->assertEquals($warnings,ADD::web()->getWarnings());
    }

    public function assertHasErrors($errors) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $this->assertEquals($errors,ADD::web()->getErrors());
    }
     public function assertHasNoWarnings() {
     	//ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
     	$warnings = ADD::web()->getWarnings();
        if (is_array($warnings) && count($warnings)>0) throw new Exception("No warnings were expected:".print_r($warnings,1));

    }
	
	public function assertHasNoErrors() {
     	//ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
     	$errors = ADD::web()->getErrors();
        if (is_array($errors) && count($errors)>0) throw new Exception("No errors were expected:".print_r($errors,1));

    }

    public function assertHasMessage($message) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());

        $this->assertEquals(array($message),ADD::web()->getMessages());
    }
    
    public function assertIncludesMessage($message) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());

        $this->assertTrue(in_array($message,ADD::web()->getMessages()));
    }
    public function assertHasMessages($messages) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());

        $this->assertEquals($messages,ADD::web()->getMessages());
    }
     public function givenPageEncounterExistsAndHasValues($values,$page=null,$functionName="child") {
            if (!$page) $page=$this->_page;
            $map = array();
            $er = $this->getERMock();

            foreach($values as $key => $value) {
                $map[]=array($key,$value);
            }
			
			$vtMap = array();
			foreach($values as $key => $value) {
				$vtMap[]=array($key,$page, $key.$value);
			}
            $er->expects($this->any())->method("getObsValue")->will($this->returnValueMap($map));
            $er->expects($this->any())->method("getObsText")->will($this->returnValueMap($vtMap));
            
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
            ADD::sys()->getEncounterManager()->setExpected($page,$functionName,$er);
            return $er;
        }
		
		public function givenDomainEncounterExists($page=null) {
            if (!$page) $page=$this->_page;
            $map = array();
            $er = $this->getERMock();

            $er->expects($this->any())->method("exists")->will($this->returnValue(true));
            ADD::sys()->getEncounterManager()->setExpected($page,"record",$er);
            return $er;
        }

        function setExpectedER($page,$function,$er,$per=null) {
            return ADD::sys()->getEncounterManager()->setExpected($page,$function,$er,$per);
        }
		
		 public function _confirmCreateDomain() {
    	$em = ADD::sys()->getEncounterManager();
	$mod = $this->_module;
    	$per = $this->getMock('ADD_EncounterRecord');
        $per->expects($this->once())->method("store");
	$encType = $this->_page->getSetting('encounter');	
		
        $em->setExpected($encType,"record",$per);
        $createSet = $this->_page->getSettingList('create-set',',');
		foreach($createSet as $createPage) {
                    $cpage = $mod->getPage($createPage);
                    $er = $this->getMock('ADD_EncounterRecord');
                    $er->expects($this->once())->method('store');
                    $em->setExpected($cpage->getSetting('encounter'),"newChild",$er);
		}
	}
	
	public function givenSelectedPageIs($url) {
		ADD::sys()->getAccessManager()->getCurrentPage($url);
	}
	public function expectsGetPatientToReturnPatient() {
		$patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN");
        ADD::db('patient')->expects($this->any())->method("getPatient")->with(123)->will($this->returnValue($patient));
	}
	public function expectsGetEncounterFormWithTypeToReturnItem($encType) {
		$encForm = array("id"=>1,"type"=>$encType);
		ADD::db('encounter')->expects($this->exactly(1))->method("getEncounterFormWithType")->with($encType)->will($this->returnValue($encForm));
	}
	
	public function expectChildrenWithPageValues($pageName,$childPageName,$valueRecords, $modName) {
		$general = ADD::sys()->getModule($modName)->getPage($pageName);
		$consent = ADD::sys()->getModule($modName)->getPage($childPageName);
		if(!$general) throw new Exception("Page '$pageName' not found in module '$modName'");
		if(!$consent) throw new Exception("Page '$childPageName' not found in module '$modName'");
		
		$em = ADD::sys()->getEncounterManager();
		$parents = array();
		$pcounter=0;
		foreach($valueRecords as $valueRecord) {
			$parents[]=$this->getERMock();
			$er = $this->getERMock();
			$valueMap = array();
			foreach($valueRecord as $key => $value) {
				$valueMap[]=array($key,$value);
			}
			$valueTextMap = array();
			foreach($valueRecord as $key => $value) {
				$valueTextMap[]=array($key,$consent, $key.$value);
			}
			$er->expects($this->any())->method("getObsValues")->will($this->returnValue($valueRecord));
			$er->expects($this->any())->method("getObsValue")->will($this->returnValueMap($valueMap));
			$er->expects($this->any())->method("getObsText")->will($this->returnValueMap($valueTextMap));
			$er->expects($this->any())->method("exists")->will($this->returnValue(true));
			ADD::log()->debug("expect $pageName -> $childPageName");
			$this->setExpectedER($consent,"child",$er,$parents[$pcounter]);
			$pcounter++;
		}
		$this->setExpectedER($general,"children",$parents);
	}

	public function expectsGetPatientToReturnDeceasedPatient() {
		$patient = array("id"=>123,"is_deleted"=>0,"first_name"=>"JAMES","last_name"=>"BROWN","deceased"=>1);
        ADD::db('patient')->expects($this->any())->method("getPatient")->with(123)->will($this->returnValue($patient));
	}

}

