<?php
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Module\Module;

class ADD_Page {
	const PID = "pid";
	const EID = "eid";
	const T = "t";
	private $_settings = null;
	private $_components = array();
	private $_permissions = array();
	private $_expectedPermissions = array();
	private $_module = null;
	private $_data = array();
	private $_setup = false;
	private $_name = null;
	private $_token = null;
	protected $_public = false;
	protected $_formOnly=false;
	protected $_origPath;
	
	function __construct($name, AbstractConfig $settings, Module $module = null) {
		$this -> setName($name);
		$this -> setModule($module);
		$this -> _settings = $settings;
        $this->events = new EventDispatcher();
	}
	function getFilePath() {
		return $this->_filePath;
	}
	function setFilePath($filePath) {
		$this->_filePath = $filePath;
	}
	function isPublic() {
		return $this -> _public;
	}
	function isFormOnly() {
		return $this->_formOnly;
	}
	function setPublic($public) {
		$this -> _public = $public;
	}

	public function html() {
		return ADD::web() -> getContent('main');
	}

	public function setToken($token) {
		$this -> _token = $token;
	}

	public function getParentPage() {
		return $this -> getParent();
	}

	public function getTitle() {
		return $this -> getSetting('title');
	}

	public function setSetup($setup) {
		$this -> _setup = $setup;
	}

	public function isSetup() {
		return $this -> _setup;
	}

	function getName() {
		return $this -> _name;
	}

	function setName($name) {
		$this -> _name = $name;
	}

	function addWarning($warning, $noDuplicates = null) {
		ADD::web() -> addWarning($warning, $noDuplicates);
	}

	function addError($error, $noDuplicates = null) {
		ADD::web() -> addError($error, $noDuplicates);
	}

	function addMessage($message, $noDuplicates = null) {
		ADD::web() -> addMessage($message, $noDuplicates);
	}

	function setMessages($messages = null) {
		ADD::web() -> setMessages($messages);
	}

	function setWarnings($warnings = null) {
		ADD::web() -> setWarnings($warnings);
	}

	function setErrors($errors = null) {
		ADD::web() -> setErrors($errors);
	}

	function getWarnings() {
		return ADD::web() -> getWarnings();
	}

	function getErrors() {
		return ADD::web() -> getErrors();
	}

	function getMessages() {
		return ADD::web() -> getMessages();
	}

	function addJavascript($javascript, $location = "document") {
		ADD::web() -> addJavascript($javascript, $location);
	}

	function getSetting($key, $default = null) {
		return $this -> _settings -> getSetting($key, $default);
	}

	function assertSetting($key) {
		return $this -> _settings -> assertSetting($key);
	}

	function getSettings() {
		return $this -> _settings -> getSettings();
	}

	function setSettings($settings) {
		return $this -> _settings -> setSettings($settings);
	}

	function setSetting($key, $value) {
		return $this -> _settings -> setSetting($key, $value);
	}

	function getSettingList($key, $sepChar = " ") {
		return $this -> _settings -> getSettingList($key, $sepChar);
	}

	function getSettingsFromArray($array, $asMap = false, $exceptionOnEmpty = false) {
		$settings = array();
		foreach ($array as $item) {
			$setting = $this -> getSetting($item);
			if ($exceptionOnEmpty && !$setting)
				throw new ADD_PageException("Setting '$item' was not found");
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
				$page = ADD::sys()->getPageFromPim($pSetting);
			}
			if(!$page) {
				throw new ADD_PageException("Parent page '$pSetting' of '{$this->getName()}' was not found in module '" . $mod -> getName() . "' or super-module '" . $mod -> getSuperModule() -> getName() . "'");
			}
		}
		return $page;
	}

	function getSiblingPage($pageName) {
		$ppage = $this -> getParentPage();
		return $ppage -> getChildPage($pageName);
	}

	function init() {
		$compCollection = new ADD_ComponentCollection($this);
		$this -> initComponents($compCollection);
	}

	function initComponents(ADD_ComponentCollection $components) {
		$components -> initialise();
		$this -> _components = $components;
	}

	function setupComponents() {
		if($this->isSetup()) return;
		try {
			$this -> _components -> callFunction("setup");
		} catch(ADD_FormDoesNotExistException $ex) {
			$this -> addWarning($ex -> getMessage());
		}
		$this->setSetup(true);
	}

	function getModule() {
		return $this -> _module;
	}

	function setModule($module) {
		$this -> _module = $module;
	}

	function getComponents() {
		return $this -> _components;
	}

	function setComponents($components) {
		$this -> _components = $components;
	}

	function hasComponent($name) {
		return $this -> _components -> has($name);
	}

	function getComponent($name) {
		return $this -> _components -> get($name);
	}

	function setComponent($name, $component) {
		$this -> _components -> add($name, $component);
	}

	function createBreadcrumb() {
		$breadcrumb = array();
		//foreach($this->getComponents() as $component) {
		$this -> getComponents() -> callFunction("breadcrumb", array(&$breadcrumb));
		//}
		if (!isset($breadcrumb) || !$breadcrumb || count($breadcrumb) == 0)
			return;
		$bcontent = ADD::web() -> getContent('breadcrumb');
		$bcount = 1;
		$bcontent -> startElement("ul", array("class" => "breadcrumb"));
		foreach ($breadcrumb as $crumb) {
			$bcontent -> addTextElement("li", $crumb);

			if ($bcount < count($breadcrumb)) {
				$bcontent -> startElement("li");
				$bcontent -> addTextElement("span", "&raquo;", array("class" => "divider"));
				$bcontent -> endElement("li");
			}

			$bcount++;
		}
		$bcontent -> endElement("ul");
	}

	function createMenuAttrs() {
		$menuAttrs = array();
		$this -> getComponents() -> callFunction("menuAttrs", array(&$menuAttrs));

		return $menuAttrs;
	}

	function userLogin(ADD_AccessManager $accessManager) {
		$this -> getComponents() -> callFunction("userLogin", array($accessManager));
	}

	function userLogout(ADD_AccessManager $accessManager) {
		$this -> getComponents() -> callFunction("userLogout", array($accessManager));
	}

	function createTitle() {
		$title = null;
		$this -> getComponents() -> callFunction("title", array(&$title));

		if (!$title)
			return;
		if (ADD::web() -> getContentClass() == "Html") {
			$tcontent = ADD::web() -> getContent('title');
			$tcontent -> addTextElement("p", $title, array("class" => "lead"));
		}
		return $title;
	}

	function createMenuPath() {
		$menuPath = null;
		$this -> getComponents() -> callFunction("menuPath", array(&$menuPath));
		return $menuPath;
	}

	function isPathNotPage() {
		$pathNotPage = null;
		foreach ($this->getComponents() as $comp) {
			$comp -> pathNotPage($pathNotPage);
		}
		return $pathNotPage;
	}

	function createViewFile() {
		$viewFile = ADD::web()->getThemeView("content");
		foreach ($this->getComponents() as $comp) {
			$comp -> viewFile($viewFile);
		}
		return $viewFile;
	}

	function createMenuItem() {
		$menuItem = array();
		$this -> getComponents() -> callFunction("menuItem", array(&$menuItem));

		return $menuItem;
	}

	function createMenuIcon() {
		$menuIcon = array();
		$this -> getComponents() -> callFunction("menuIcon", array(&$menuIcon));
		return $menuIcon;
	}

	function createMenuLabel() {
		$menuLabel = null;
		$this -> getComponents() -> callFunction("menuLabel", array(&$menuLabel));
		return $menuLabel;
	}

	function afterSubmit(ADD_Page $submitPage) {
		$this -> getComponents() -> callFunction("afterSubmit", array(&$submitPage));
	}

	function createPermissions() {
		$permissions = array();
		$this -> getComponents() -> callFunction("permissions", array(&$permissions));
		return $permissions;
	}

	function createContent() {
		$add = ADD::sys();

		$am = $add -> getAccessManager();

		$components = $this -> getComponents();
		$components -> callFunction("displayAccess");
		$components -> callFunction("beforePopulate");

		$components -> callFunction("populate");

		$components -> callFunction("afterPopulate");

		$components -> callFunction("beforeAllGenerate");

		$components -> callFunction("beforeGenerate");

		$components -> callFunction("generate");

		$components -> callFunction("afterGenerate");

		$components -> callFunction("afterAllGenerate");

		return $this -> html();
	}

	function createMenu() {
		$options = ADD::web() -> newContent();
		$this -> getComponents() -> callFunction("menu", array($options));

		$this -> getComponents() -> callFunction("afterMenu", array($options));

		return $options;
	}

	function createOutput($values = array()) {
		$content = null;
		foreach ($this->getComponents()->asArray() as $name => $comp) {
			$content = $comp -> output($values);
		}
		if (!$content)
			return ADD::web() -> getContent();
		return $content;
	}

	function createContentType() {

		$contentType = "Html";
		foreach ($this->getComponents() as $name => $component) {
			if (method_exists($component, "contentType"))
				$component -> contentType($contentType);
		}

		return $contentType;
	}

	function isWebContent() {
		if ($this -> createContentType() == "Html") {
			return true;
		}
		return false;
	}

	function checkPublicPage() {
		$am = ADD::sys() -> getAccessManager();

		if (!$am -> isLoggedIn() && !$this -> isPublic()) {
			throw new \ClinicLE\Page\Exception\PublicPageException("Sorry you must be logged in to access this page.<br/><br/><a class='btn' href='/?dest=".urlencode(ADD_Util::currentPageURL())."'><i class='icon-home'></i>&nbsp;Go to home</a>");
		}
	}

	function submitForm($values) {
		$add = ADD::sys();
		$db = ADD::db();
		$results = array();
		$origValues = $values;

		foreach ($this->getComponents() as $comp) {
			$comp -> submitAccess($values);
			if ($add -> hasState('cancel')) {
				ADD::db('token') -> unregister();
				return;
			}
		}
		foreach ($this->getComponents() as $comp) {
			$msgs = $comp -> beforeSubmit($values);
			if ($msgs && count($msgs) > 0) {
				foreach ($msgs as $msg) { $this -> addError($msg);
				}
				return;
			}
			if ($add -> hasState('cancel')) {
				ADD::db('token') -> unregister();
				return;
			}
		}
		foreach ($this->getComponents() as $comp) {
			$compResults = $comp -> submit($values);
			if ($compResults)
				$results = $results + $compResults;
			if ($add -> hasState('cancel')) {
				ADD::db('token') -> unregister();
				return;
			}
		}
		$add -> setState('submitted', true);

		ADD::db('token') -> unregister();
		return $results;
	}

	function hasAnyPermissions($permList) {
		if (!$permList)
			return false;

		foreach ($permList as $permItem) {
			if ($this -> getPermission($permItem))
				return true;
		}
		return false;
	}

	function getPermissions($permList = null) {
		if (!$permList)
			return $this -> _permissions;
		$perms = array();
		foreach ($permList as $permItem) {
			$perms[] = $this -> getPermission($permItem);
		}
		return $perms;
	}

	function setPermissions($permissions) {
		$this -> _permissions = $permissions;
	}

	function setPermission($permission, $flag) {
		$this -> _permissions[$permission] = $flag;
	}

	function getPermission($permission) {
		return isset($this -> _permissions[$permission]) ? $this -> _permissions[$permission] : false;
	}

	function getExpectedPermissions() {
		$perms = array();
		$expectedPerms = $this->_expectedPermissions;
		foreach ($this->getComponents() as $component) {
			$component -> expectedPermissions($expectedPerms);
		}
		return $expectedPerms;
	}

	function setExpectedPermissions($expectedPermissions) {
		$this -> _expectedPermissions = $expectedPermissions;
	}

	public function getData() {
		return $this -> _data;
	}

	public function setData($data) {
		$this -> _data = $data;
	}

	function unsetData($name) {
		if (isset($this -> _data[$name]))
			unset($this -> _data[$name]);
	}

	function createMenuParams() {
		$params = array();
		foreach ($this->getComponents() as $comp) {
			$comp -> menuParams($params);
		}
		return $params;
	}

	function createMenuUri($extraParams = array()) {
		$params = $this -> createMenuParams();
		foreach ($extraParams as $extraName => $extraValue) {
			$params[$extraName] = $extraValue;
		}
		return ADD_Util::uri($this -> createMenuPath(), $params);
	}

	public function getSystem() {
		return ADD::sys();
	}

	public function getEncounterRecord($idValue = null) {
		if ($this -> hasComponent('Encounter')) {
			return $this -> getComponent('Encounter') -> getEncounterRecord($idValue);
		}
	}

	function populateUserGroups($groups, $isAdmin) {
		$groupByName = array();

		foreach ($groups as $group) {
			$groupByName[$group -> name] = true;
		}

		$pagePerms = array();
		$mod = $this -> getModule();
		foreach ($this->getExpectedPermissions() as $perm) {
			$expPerm = $perm . "_" . $mod -> getName() . "_" . $this -> getName();
			if (isset($groupByName[$expPerm]) || $isAdmin) {
				$pagePerms[$perm] = true;
			}
		}
		$this -> setPermissions($pagePerms);
	}

	function redirectUsingSetting($setting = "redirect") {
		$mod = $this -> getModule();
		$redirect = $this -> getSetting($setting);
		if (!$redirect)
			return;
		$rpage = $mod -> getPage($redirect);
		if (!$rpage || ($rpage && !$rpage -> getPermission('create')))
			return;

		ADD::sys() -> setState('redirect', $rpage -> createMenuUri());
	}

	function createBaseContent() {
		$this -> getComponents() -> callFunction("baseContent");
	}

	function isActiveMenu($currentSectionER = null) {
		$currPage = ADD::sys() -> getCurrentPage();
		if(!$currPage) throw new ADD_PageNotFoundException("Current page is not defined");
		$isActive = null;

		foreach ($this->getComponents() as $comp) {
			$comp -> activeMenu($isActive, $currPage, $currentSectionER);
		}
		return $isActive;
	}
    function getEvents() {
        return $this->events;
    }
	
	function getPim() {
		return $this->getModule()->getAlias().":".$this->getName();
	}
}
?>
