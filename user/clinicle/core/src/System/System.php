<?php

/**
 * The System class is a singleton which acts as a registry for ADD.
 * 
 * The System class requires a path and profile to initialise. It then loads the configuration files for that profile.
 * Then it provides access to all Module classes loaded into the ADD framework.
 * And access to the manager classes including AccessManager and EncounterManager.
 *
 * @package Main
 * @example $sys = new ADD_System("/local/add","add-test");
 * @version 2.00
 * @since 14/03/2014
 * @author Rob Free
 */
 
namespace ClinicLE\System;

use ClinicLE\System\Exception\ServiceMissingException;
use ClinicLE\System\Application;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Environment\AbstractEnvironment;
use ClinicLE\Common\Exception\NullValueException;
use ClinicLE\System\Service\CoreServiceProvider;
use ClinicLE\Module\ModuleSetup;

use Pimple\Container;

class System {
	private $environment;
	private $serviceContainer;
	private $moduleStructure;
	private $application;
	private $eventDispatcher;
	private $patientId;
	private $parentEncounterId;
	private $params = array();
	private $init = false;
	private $serviceContainerInit = false;
	
	function __construct(AbstractEnvironment $environment, Container $serviceContainer, ModuleStructure $moduleStructure, Application $application, EventDispatcher $eventDispatcher) {
		$this->environment = $environment;
		$this->serviceContainer = $serviceContainer;
		$this->moduleStructure = $moduleStructure;
		$this->application = $application;
		$this->eventDispatcher = $eventDispatcher;
	}

	function clearCache() {
		$this->getService(CoreServices::CACHE)->clear();
	}

	function getFromCache($key) {
		//ADD::log()->info("get from Cache:".get_class($this->_cache)." - $key");
		return $this->getService(CoreServices::CACHE)->get($key);
	}

	function putInCache($key, $data) {
		//ADD::log()->info("put in Cache:".get_class($this->_cache)." - $key:");

		return $this->getService(CoreServices::CACHE)->put($key, $data);
	}

	function isInCache($key) {

		return $this->getService(CoreServices::CACHE)->exists($key);
	}

	function getService($serviceName) {
		$service = $this->serviceContainer[$serviceName];

		if (!$service) {
			throw new ServiceMissingException("Service '$serviceName' has not been defined");
		}
		return $service;
	}

	/**
	 * Utility function to retrieve the current page from the AccessManager
	 * @param string $urlPath
	 * @return ADD_Page
	 * @deprecated
	 */
	function getCurrentPage($urlPath = null) {
		return $this->getService(CoreServices::ACCESS_MANAGER)->getCurrentPage($urlPath);
	}

	/**
	 * Set a particular global state in ADD
	 * @param string $state
	 * @return any
	 */
	function hasState($state) {
		return $this->getService(CoreServices::STATES)->hasState($state);
	}

	/**
	 * Ensure GET parameter was previously defined in the list and return it
	 * @param string $name
	 * @return string|array
	 * @throws ADD_SystemException
	 */
	function assertParam($name) {
		$param = $this->getParam($name);
		if ($param === null) {
			throw new NullValueException("URL parameter '$name' is not set and is mandatory");
		}
		return $param;
	}

	/**
	 * Get GET parameter or a default value if it not present
	 * @param string $param
	 * @param string|array $default
	 * @return string|array
	 */
	function getParam($param, $default = null) {
		return isset($this->params[$param]) ? $this->params[$param] : $default;
	}

	/**
	 * Get either all GET parameters (as associative list) or a subset of parameters as an ordered array
	 * @param array $params
	 * @param string|array $default
	 * @return array
	 */
	function getParams($params = null, $default = null) {
		if (!$params) {
			return $this->params;
		}
		if (!is_array($params)) {
			return;
		}
		$vals = array();
		foreach ($params as $param) {
			$vals[] = $this->getParam($param, $default);
		}
		return $vals;
	}

	/**
	 * Assert GET subset of parameters exist and return as an ordered array
	 * @param array $params
	 * @param string|array $default
	 * @return array
	 */
	function assertParams($params, $default = null) {
		if (!$params) {
			return $this->params;
		}
		$vals = array();
		foreach ($params as $param) {
			$vals[] = $this->assertParam($param, $default);
		}
		return $vals;
	}

	/**
	 * Set GET parameter with a value
	 * @param string $param
	 * @param string|array $value
	 */
	function setParam($param, $value) {
		$this->params[$param] = $value;
	}

	/**
	 * Set GET parameters list
	 * @param array $params
	 */
	function setParams($params) {
		if (isset($params['pid']))
			$this->setPatientId($params['pid']);
		if (isset($params['eid']))
			$this->setParentEncounterId($params['eid']);
		$this->params = $params;
	}

	/**
	 * Set particular state
	 * @param type $state
	 * @param type $flag
	 */
	function setState($state, $flag) {
		return $this->getService(CoreServices::STATES)->setState($state, $flag);
	}

	/**
	 * Utility method to return a specific page in a specific module
	 * @param string $modName
	 * @param string $pageName
	 * @return ADD_Page
	 */
	function getPageInModule($modName, $pageName) {
		return $this->getModules()->getPageInModule($modName, $pageName);
	}

	/**
	 * Set system path (e.g. /local/add)
	 * @param string $path
	 */
	function setPath($path) {
		$this->environment->setPath($path);
	}

	/**
	 * Get system path
	 * @return string
	 */
	function getPath() {
		return $this->environment->getPath();
	}

	/**
	 * Set system profile (e.g. uhl-local)
	 * @param string $profile
	 */
	function setProfile($profile) {
		$this->environment->setProfile($profile);
	}

	/**
	 * Get system profile
	 * @return string
	 */
	function getProfile() {
		return $this->environment->getProfile();
	}

	/**
	 * If required initialises config file and gets or sets named config item or section. If not present returns null
	 * @param string $item
	 * @param string|array $value
	 * @return string|array
	 */
	function config($item, $value = null) {
		$config = $this->environment->getConfig();
		if ($value) {
			$config->setSetting($item, $value);
		}
		return $config->getConfig($item);
	}

	function getConfig() {
		return $this->environment->getConfig();
	}

	function setConfig($config) {
		$this->environment->setConfig($config);
	}

	/**
	 * Similar to 'config' function but throw Exception if item does not exist
	 * @param type $item
	 * @return type
	 * @throws ADD_SystemException
	 */
	function assertConfig($item) {
		return $this->environment->getConfig()->assert($item);
	}

	/**
	 * Utility method to get path to current config (e.g. /local/add/config/test-site)
	 * @return string
	 * @throws ADD_SystemException
	 */
	function configPath() {
		return $this->environment->getConfigPath();
	}

	function initServiceContainer() {
		if($this->serviceContainerInit) {
			return;
		}
		$this->serviceContainer->register(new CoreServiceProvider($this));
		$this->serviceContainerInit = true;
	}
	
	function initModules() {
		$moduleSetup = new ModuleSetup($this);
		$moduleSetup->loadAllModulesFromConfig($this->config('inplace'));
	}
	
	/**
	 * Initialise modules using the configuration path and profile settings.
	 * If the 'inplace' config setting is present it will load custom modules from the 'source_module_path'.
	 * If not, it will load custom module from the 'module_path' sub path (e.g. /local/add/work)
	 */
	function initialise() {
		$this->initServiceContainer();
		$this->initModules();
	}

	/**
	 * Get Module from the ModuleCollection
	 * @param string $name
	 * @return \ADD_Module
	 */
	function getModule($name) {
		return $this->moduleStructure->getModules()->get($name);
	}

	/**
	 * Set the current ModuleCollection
	 * @param \ADD_ModuleCollection $modules
	 */
	function setModules(\ADD_ModuleCollection $modules) {
		$this->moduleStructure->setModules($modules);
	}

	/**
	 * Get the AccessManager
	 * @return \ADD_AccessManager
	 */
	function getAccessManager() {
		return $this->getService(CoreServices::ACCESS_MANAGER);
	}

	/**
	 * Get the EncounterManager
	 * @return \ADD_EncounterManager
         * @deprecated since version 2.11.0
	 */
	function getEncounterManager() {
		return $this->getService(CoreServices::DATA_MANAGER);
	}
        
        /**
	 * Get the EncounterManager
	 * @return \ADD_EncounterManager
	 */
	function getDataManager() {
		return $this->getService(CoreServices::DATA_MANAGER);
	}

	/**
	 * Get the ModuleCollection
	 * @return \ADD_ModuleCollection
	 */
	function getModules() {
		return $this->moduleStructure->getModules();
	}

	/**
	 * Get the patient ID
	 * @return integer
	 */
	function getPatientId() {
		return $this->patientId;
	}

	/**
	 * Set the patient ID
	 * @param integer $patientId
	 */
	function setPatientId($patientId) {
		$this->patientId = $patientId;
	}

	function setParentEncounterId($parentEncounterId) {
		$this->parentEncounterId = $parentEncounterId;
	}

	function getParentEncounterId() {
		return $this->parentEncounterId;
	}

	function errorMessage($ex) {
		$message = $ex->getMessage();
		if ($this->config('nocatch'))
			throw $ex;
		if ($this->config('stacktrace'))
			$message.="<br/>Stacktrace:<pre>" . $ex->getTraceAsString() . "</pre>";
		return $message;
	}

	/**
	 * Extracts all pages in all modules as an associative array
	 * @return array
	 */
	function getPagesInModules() {
		return $this->moduleStructure->getPagesInModules();
	}

	function execInBackground($execDir, $execCommand) {
		chdir($execDir);
		$output = $this->getService(CoreService::PROCESS)->exec($execCommand . " >/tmp/log1 2>/tmp/log2 &");
		return $output;
	}

	function isProcessRunning($processName) {
		$op = array();
		$command = 'ps aux | grep "' . $processName . '"';

		$this->getService(CoreServices::PROCESS)->exec($command, $op);
		if (count($op) > 2)
			return true;
		return false;
	}

	function isCorrectVersion() {
		return $this->environment->isCorrectVersion();
	}

	function getInstalledVersion() {
		return $this->environment->getInstalledVersion();
	}

	function executeManageTool($command, $options) {
		$sysCommand = "export ADD_PATH=" . $this->getPath() . ";export ADD_PROFILE=" . $this->getProfile() . "; " . $this->getPath() . "/vendor/bin/add-manage $command $options";
		$this->getService(CoreServices::PROCESS)->execWithException($sysCommand, false, "Unable to execute add-manage $command");
	}

	function getSourceModulePath($modName = null) {
		return $this->environment->getSourceModulePath($modName);
	}

	function getWorkModulePath($modName = null) {
		return $this->environment->getLiveModulePath($modName);
	}

	function getWebAppPath($phpFile = "") {
		return $this->environment->getWebAppPath($phpFile);
	}

	function getPageFromPim($pim) {
		return $this->moduleStructure->getPageFromPim($pim);
	}

	function getServiceContainer() {
		return $this->serviceContainer;
	}
	function setServiceContainer($serviceContainer) {
		$this->serviceContainer = $serviceContainer;
	}
	function getEnvironment() {
		return $this->environment;
	}

	/**
	 * @deprecated
	 * @return EventDispatcher
	 */
	function getEventDispatcher() {
		return $this->eventDispatcher;
	}

	function getEvents() {
		return $this->eventDispatcher;
	}
	function getApplication() {
		return $this->application;
	}
	
	function db($name=null) {
		return $this->getService(CoreServices::DATABASE_MANAGER)->get($name);
	}
	
	function isInit() {
		return $this->init;
	}
	
	function setInit($init) {
		$this->init = $init;
	}
	
	function getFactory($factoryType) {
		return $this->getService(CoreServices::FACTORY_REGISTRY)->getFactory($factoryType);
	}
	
	function newFactoryInstance($factoryType, $className, $arguments = null) {
		return $this->getService(CoreServices::FACTORY_REGISTRY)->newInstance($factoryType, $className, $arguments);
	}
}

?>
