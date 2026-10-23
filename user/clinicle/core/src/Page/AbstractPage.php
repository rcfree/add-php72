<?php
namespace ClinicLE\Page;

use ClinicLE\Common\Exception\SettingNotDefinedException;
use ClinicLE\Common\Exception\UserExceptionInterface;
use ClinicLE\Page\Exception\PageDefinitionException;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Module\Module;
use CLE;
use ClinicLE\Security\Access;
use ClinicLE\System\Exception\AbstractUserException;
use ClinicLE\System\Exception\MethodNotImplementedException;
use ClinicLE\System\Service\SessionStorage;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractPage
{

    protected $settings = null;
    protected $module = null;
    protected $setup = false;
    protected $name = null;
    protected $data;

    /**
     * @var Access
     */
    protected $access;
    protected $filePath;

    function __construct($name, AbstractConfig $settings, Module $module = null) {
        $this -> setName($name);
        $this -> setModule($module);
        $this -> settings = $settings;
        $this->events = new EventDispatcher();
        $this->access = $this->createAccess();
    }

    /**
     * @return Access
     */
    abstract function createAccess();

    function getFilePath() {
        return $this->filePath;
    }
    function setFilePath($filePath) {
        $this->filePath = $filePath;
    }

    public function getParentPage() {
        return $this -> getParent();
    }

    public function getTitle() {
        return $this -> getSetting('title');
    }

    public function setSetup($setup) {
        $this -> setup = $setup;
    }

    public function isSetup() {
        return $this -> setup;
    }

    function getName() {
        return $this -> name;
    }

    function setName($name) {
        $this -> name = $name;
    }

    function getSetting($key, $default = null) {
        return $this -> settings -> getSetting($key, $default);
    }

    function assertSetting($key) {
        return $this -> settings -> assertSetting($key);
    }

    function getSettings() {
        return $this -> settings -> getSettings();
    }

    function setSettings($settings) {
        return $this -> settings -> setSettings($settings);
    }

    function setSetting($key, $value) {
        return $this -> settings -> setSetting($key, $value);
    }

    function getSettingList($key, $sepChar = " ") {
        return $this -> settings -> getSettingList($key, $sepChar);
    }

    function getSettingsFromArray($array, $asMap = false, $exceptionOnEmpty = false) {
        $settings = array();
        foreach ($array as $item) {
            $setting = $this -> getSetting($item);
            if ($exceptionOnEmpty && !$setting)
                throw new SettingNotDefinedException("Setting '$item' was not found");
            if ($asMap) {
                $settings[$item] = $setting;
            } else {
                $settings[] = $setting;
            }
        }
        return $settings;
    }

    function getChildPages() {
        return $this -> getModule() -> getChildPages($this);
    }

    function getChildPage($name) {
        $cpages = $this -> getChildPages();
        foreach ($cpages as $cpage) {
            if ($cpage -> getName() == $name)
                return $cpage;
        }
    }

    function getParent() {
        $mod = $this -> getModule();
        $pSetting = $this -> getSetting('parent');
        $page = null;
        if ($pSetting) {
            $page = $mod -> getPage($pSetting);
            if (!$page)
                $page = $mod -> getSuperModule() -> getPage($pSetting);
            if (!$page) {
                $page = CLE::sys()->getPageFromPim($mod->getName().":".$pSetting);
            }
            if(!$page) {
                throw new PageDefinitionException("Parent page '$pSetting' of '{$this->getName()}' was not found in module '" . $mod -> getName() . "' or super-module '" . $mod -> getSuperModule() -> getName() . "'");
            }
        }
        return $page;
    }

    function getSiblingPage($pageName) {
        $ppage = $this -> getParentPage();
        return $ppage -> getChildPage($pageName);
    }

    function init() {}
    
    function getModule() {
        return $this -> module;
    }

    function setModule($module) {
        $this -> module = $module;
    }

    function createTitle() {
        return null;
    }

    abstract function createMenuPath();

    function initialiseOutput() {}

    function finaliseOutput($output) {
        return $output;
    }

    abstract function processError(UserExceptionInterface $ex);

    function processRequest(Request $request) {
        $method = $request->getMethod();
        $functionName = "process".ucfirst(strtolower($method)."Method");
        return $this->$functionName($request);
    }
    function processGetMethod(Request $request) {
        throw new MethodNotImplementedException("Process 'get' method not defined");
    }

    function processPostMethod(Request $request) {
        throw new MethodNotImplementedException("Process 'post' method not defined");
    }

    function processPutMethod(Request $request) {
        throw new MethodNotImplementedException("Process 'put' method not defined");
    }
    function processDeleteMethod(Request $request) {
        throw new MethodNotImplementedException("Process 'delete' method not defined");
    }

    function hasAnyPermissions($permList) {
        return $this->access->hasAnyPermissions($permList);
    }

    function getPermissions($permList = null) {
        return $this->access->getPermissions($permList);
    }

    function setPermissions($permissions) {
        $this -> access->setPermissions($permissions);
    }

    function setPermission($permission, $flag) {
        $this -> access->setPermission($permission,$flag);
    }

    function getPermission($permission) {
        return $this->access->getPermission($permission);
    }

    function getExpectedPermissions() {
        if(!$this->access) {
            throw new \Exception($this->getPim());
        }
        return $this->access->getAvailablePermissions();
    }

    function setExpectedPermissions($expectedPermissions) {
        $this -> access-> setAvailablePermissions($expectedPermissions);
    }

    function getEvents() {
        return $this->events;
    }

    function getPim() {
        return $this->getModule()->getAlias().":".$this->getName();
    }

    /**
     * @return Access
     */
    public function getAccess()
    {
        return $this->access;
    }

    public function setup() {

    }

    function createMenu() {

    }

    public function getData() {
        return $this -> data;
    }

    public function setData($data) {
        $this -> data = $data;
    }

    function unsetData($name) {
        if (isset($this -> data[$name]))
            unset($this -> data[$name]);
    }
    function isPathNotPage() {
        return false;
    }

    function getForm() {
        return $this->getComponent('Form');
    }
    function getCurrentPage(Request $request) {
        return $this;
    }
    function isStoredAsCurrent() {
        return true;
    }
    function getJavascriptIdent() {
        return "content";
    }
    function appendToResponse(Response $response) {
        $am = CLE::sys()->getAccessManager();
        if(!$am->getSession()->getUserData(SessionStorage::SESSION_EXPIRED)) {
            return;
        }
        CLE::info("Adding expired to request");
        //$response->headers->set("Clinicle-Expired",1);
    }
}
?>