<?php

abstract class ADD_Test_ComponentBase extends PHPUnit_Framework_TestCase {
	protected $_formMocker = null;
	protected $_modName = null;
	protected $_pageName = null;
	protected $_modules;
	protected static $_db = null;
	
	public function getAllAvailableDatabaseTypes() {
		return array("encounter","patient","audit","dictionary","token","history","search_patient");
	}
	public function setupMockDatabaseManager() {
		$dbm['BASE']=$this->getMock('ADD_Database');
		foreach($this->getAllAvailableDatabaseTypes() as $availableDbType) {
			$dbm[$availableDbType]=$this->getMock('ADD_Database_'.ADD_Util::convertToPascal($availableDbType));
		}
		$dbm['dictionary']->expects($this->any())->method("getDictionaryItems")->will($this->returnCallback(function($name) { return 
                array(
                    array("value"=>"1","title"=>$name."1"),
                    array("value"=>"2","title"=>$name."2"),
                    array("value"=>"3","title"=>$name."3"),
                    array("value"=>"4","title"=>$name."4"),
                    array("value"=>"5","title"=>$name."5")
        ); }));
		
        $dbm['dictionary']->expects($this->any())->method("getDictionaryGroups")->will($this->returnValue(array(array("id"=>"1","name"=>"group1","title"=>"Group 1"),array("id"=>"2","name"=>"group2","title"=>"Group 2"))));
        
        $dbm['dictionary']->expects($this->any())->method("hasDictionary")->will($this->returnValue(true));
                
		
		$dbmMap = array_map(function($key,$value) { return array($key,$value); },array_keys($dbm),$dbm);
		
        $dbmMock = $this->getMock('ADD_DatabaseManager',array('get'));
        $dbmMock->expects($this->any())->method("get")->will($this->returnValueMap($dbmMap));
		return $dbmMock;
	}
	public function expectDictionaryItemsInGroup() {
		ADD::db('dictionary')->expects($this->any())->method("getDictionaryItemsInGroup")->will($this->returnCallback(function($dictName,$groupName) {
            return array(
                array("id"=>1,"value"=>"1","title"=>$dictName.$groupName."1"),
                    array("id"=>2,"value"=>"2","title"=>$dictName.$groupName."2"),
                    array("id"=>3,"value"=>"3","title"=>$dictName.$groupName."3")
                );
        }));
	}
	public function setupMockSystem(ADD_ModuleCollection $modules) {
		$am = new ADD_AccessManager();
		
        $user = new stdClass();
        $user->username=trim(shell_exec('whoami'));
        $user->first_name="System";
        $user->last_name="User";
        $am->setUser($user);
        
		$sys = $this->getMock(
			"ADD_System",
			array("execInBackground","isProcessRunning"),
			array(getenv('ADD_PATH'),getenv('ADD_PROFILE'))
		);
        $config = new ADD_Config();
        $config->loadFile(getenv("ADD_PATH")."/config/".getenv('ADD_PROFILE')."/add.ini");
        $sys->setConfig($config);
        $config->setSetting('stacktrace',0);
        $config->setSetting('show_field_names',0);
		$config->setSetting('error_message_only',1);
		$config->setSetting('email_on_error',null);
        $sys->expects($this->any())->method("getPath")->will($this->returnValue(getenv('ADD_PATH')));
        $sys->expects($this->any())->method("getProfile")->will($this->returnValue(getenv('ADD_PROFILE')));
        $sys->expects($this->any())->method("execInBackground");
		$sys->setParams($this->_params);
		
        $em = new ADD_Test_MockEncounterManager();
        $sys->setEncounterManager($em);
        
		$sys->setModules($modules);
        $sys->setAccessManager($am);	
		return $sys;
	}
	
	public function setupMockInitialiser(ADD_System $sys, ADD_DatabaseManager $dbm) {
		$init = $this->getMock('ADD_InitialiserInterface');
		$init->expects($this->any())->method("getSystem")->will($this->returnValue($sys));
        $init->expects($this->any())->method("getWeb")->will($this->returnValue(new ADD_Web($this->getMockClassType())));
        $init->expects($this->any())->method("getDatabaseManager")->will($this->returnValue($dbm));  
		return $init;
	}
	public function getMockClassType() {
		return "Html";
	}
    public function setUp() {
		$testInfo = getenv('ADD_PAGE_TEST');
		$testModPath=getenv('ADD_MOD_PATH_TEST');
		$testDepPath=getenv('ADD_DEP_PATH_TEST');
		if (!$testInfo) throw new Exception("ADD_PAGE_TEST not set");
		if (!$testModPath) throw new Exception("ADD_MOD_PATH_TEST not set");
		
		$moduleCollection = new ADD_ModuleCollection();
		
		$dbm = $this->setupMockDatabaseManager();
		$sys = $this->setupMockSystem($moduleCollection);
		$init = $this->setupMockInitialiser($sys,$dbm);
		
		ADD::init($init);
		if (!$testDepPath) $testDepPath = ADD::sys()->getWorkModulePath();
		
		list($modName,$pageName)=explode(":",$testInfo);
		
		$moduleSetup = new ADD_ModuleSetup($sys);
		
		$this->_module = $moduleSetup->loadSingleModuleWithDependencies($modName,$testModPath,$testDepPath);
		
		$this->_formMocker = new ADD_Test_FormMocker($moduleCollection);
		
		$this->_modName=$modName;
        $this->_pageName=$pageName;
		
		$this->_formMocker->addFormMocksToModules();
		
		$currPage = $this->_module->getPage($this->_pageName);
        
		if (!$currPage) throw new Exception("Page '{$this->_pageName}' was not found in module '".$this->_module->getName()."'");
        
        if ($currPage->hasComponent('Content')) {
            $currPageUrl = $currPage->createMenuPath();
            $page = ADD::sys()->getAccessManager()->getCurrentPage($currPageUrl);
        }
        else {
            $page = $currPage;
        }
        
        $this->_page = $page;
        $this->_formMocker->addFormDependency($page);
		
		$this->setupEncounterRecord();
    }

    public function assertHasContent($content,$name="main") {
        $this->assertEquals(ADD::web()->getContent($name)->output(),$content);
    }
    public function assertHasWarning($warning) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $this->assertEquals(array($warning),ADD::web()->getWarnings());
    }
	
	public function assertHasWarnings($warnings) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        //ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
        $this->assertEquals($warnings,ADD::web()->getWarnings());
    }
    
     public function assertHasNoWarnings() {
     	//ADD::log()->info("warn:".json_encode(ADD::web()->getWarnings()));
     	$warnings = ADD::web()->getWarnings();
        if (is_array($warnings) && count($warnings)>0) throw new Exception("No warnings were expected:".print_r($warnings,1));
        
    }
	 
	  public function assertHasNoMessages() {
     	$messages = ADD::web()->getMessages();
        if (is_array($messages) && count($messages)>0) throw new Exception("No messages were expected:".print_r($messages,1));
        
    }
    
    public function assertHasMessage($message) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        
        $this->assertEquals(array($message),ADD::web()->getMessages());
    }
    
    function getFieldsFromHtml($htmlContent) {
        $htmlKeyedList = $htmlContent->getHtmlList();
        $htmlList = array_values($htmlKeyedList);
        $fields = array();
        $hcount = 0;
		$checks = array();
        while($hcount<count($htmlList)) {
            $html = $htmlList[$hcount];
            $tag = ADD_Util::val("tag",$html);
            
            if (is_object($html) || ($tag!="input" && $tag!="select")) {
               $hcount++; 
               continue;
            }
            $attrs = ADD_Util::val("attrs",$html);
            list($type,$value,$name)=ADD_Util::vals(array("type","value","name"),$attrs);
            if ($tag=="select") {
                $hcount++;
                $opt = $htmlList[$hcount];
                
                while(ADD_Util::val('tag',$opt)=="option") {
                    if (ADD_Util::val("selected",$opt['attrs'])=="selected") {  
                        $fields[$name]=ADD_Util::val("value",$opt['attrs']);
                    }
                    $opt=$htmlList[$hcount];
                    $hcount++;
                }            
                
            }
            if ($tag=="input") {
                if ($type=="radio") {
                    if (!isset($fields[$name])) $fields[$name]="";
                    if (ADD_Util::val("checked",$attrs)=="checked") $fields[$name]=$value;
                }
                elseif($type=="checkbox") {
                    $name=preg_replace("/\[\d+\]/","",$name);
                    if (!isset($fields[$name])) {
                    	$fields[$name]=array();
						$checks[$name]=0;
					}
					$checks[$name]++;
					 if (ADD_Util::val("checked",$attrs)=="checked") $fields[$name][$value]=$value;
                }
                else {
                    $fields[$name]=$value;
                }
            }
			
           $hcount++;
        }
		foreach($checks as $name=>$ccount) {
				if ($ccount!=1) continue;
				if (count($fields[$name])==0) {
					$fields[$name]=0;
				}
				else {
					$fields[$name]=1;
				}
			}
        return $fields;
    }
}
