<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AbstractBaseTest
 *
 * @author rcf8
 */
	
namespace ClinicLE\Testing;

use ClinicLE\System\Environment\AbstractEnvironment;
use ClinicLE\System\Initialiser;
use ClinicLE\System\Service\CoreServices;
use CLE;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Module\Module;
use ClinicLE\Page\PageCollection;
use ClinicLE\Common\Util;

/**
 * Description of DatabaseStorageTest
 *
 * @author rcf8
 */


abstract class AbstractBaseTest extends \PHPUnit_Framework_TestCase {
	public $module;
	public $pages;
	
	function addMockDatabase($dbName,$dbClassName) {
		$dbObject = $this->getMock($dbClassName);
		CLE::sys()->getService(CoreServices::DATABASE_MANAGER)->set($dbName,$dbObject);
	}
	
	function initMockSystem($settings=array()) {
        CLE::log($this);
		$Environment = new TestEnvironment();
		$config = new ArrayConfig($settings);
		$Environment->setConfig($config);
		$system = CLE::init(new Initialiser($Environment));
		$services = $system->getServiceContainer();
		$dbm = new TestDatabaseManager();
		$services[CoreServices::DATABASE_MANAGER]=$dbm;
		return $system;
	}
	
	function initTestServices() {
		$system = CLE::sys();
		$serviceContainer = $system->getServiceContainer();
		$serviceContainer[CoreServices::CONFIG]=$system->getConfig();
		$serviceContainer->register(new Service\TestServiceProvider($system));
	}
	function addMockService($serviceName, $serviceClass, $disableArgs = false) {
		$serviceContainer = CLE::sys()->getServiceContainer();
		$mockBuilder = $this->getMockBuilder($serviceClass);
		if($disableArgs) {
			$mockBuilder->disableOriginalConstructor();
		}
		$mockService = $mockBuilder->getMock();
		$serviceContainer[$serviceName] = $mockService;
		return $mockService;
	}
	public function initTestModule($modName,$modSettings) {
		$config = new ArrayConfig($modSettings);
		$module = new Module($modName,$config);
		$pages = new \ADD_PageCollection($module);
		$module->initPages($pages);
		foreach($module->getPages() as $page) {
			$page->init();
		}
		CLE::sys()->getModules()->add($module);
		return $module;
	}
	
	public function givenPatientIsSelected($patient) {
		CLE::sys()->setPatientId($patient['id']);
		$this->currentPatient->expects($this->any())->method("getCurrentPatient")->will($this->returnValue($patient));
	}
	
	 
    public function assertHasContent($content,$name="main") {
        $this->assertEquals(CLE::web()->getContent($name)->output(),$content);
    }
    public function assertHasWarning($warning) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        
        $this->assertEquals(array($warning),CLE::web()->getWarnings());
    }
    
    public function assertHasNoWarnings() {
        $this->assertTrue(Util::isEmpty(CLE::web()->getWarnings()));
    }
    
    public function assertHasMessage($message) {
        //$//proErrors = $this->removeStackTrace(ADD::web()->getWarnings());
        
        $this->assertEquals(array($message),CLE::web()->getMessages());
    }
	
	public function givenTokenExists() {
		$this->request->query->set("t","TOKEN123");
	   $this->tokenService->expects($this->once())->method("register")->will($this->returnValue(TokenResult::PRESENT));
	}
	
	 public function givenTokenDoesNotExist() {
		$this->request->query->set("t","TOKEN123");
	   $this->tokenService->expects($this->once())->method("register")->will($this->returnValue(TokenResult::SUCCESS));
	}
  
    
}
class TestEnvironment extends AbstractEnvironment {
	public function __construct() {
		parent::__construct("/path/to/test","test-profile");
	}
}
class TestDatabaseManager {
	function set($dbName,$dbClass) {
		$this->db[$dbName]=$dbClass;
	}
	function get($dbName) {
		return $this->db[$dbName];
	}
}
