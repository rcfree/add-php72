<?php

abstract class ADD_Component {
	const ACTION="__action";
	const ID="__id";
	const PARENT_ID = "__parent_id";
	const NEW_ID = "__new_id";
	const INVALID = "__invalid";
	const AUTOSAVE = "__autosave";
	const PATIENT_ID = "__pid";
	const VALIDATE = "__validate";
	const TOKEN = "__token";
	
	protected $_setup = false;
	protected $_componentList = array();
	protected $_page = null;

    function __construct(ADD_Page $page=null) {
        if ($page) $this->_page = $page;
    }
	function init() {
		
	}
    function getPage() {
        return $this->_page;
    }
    function setPage(ADD_Page $page) {
        $this->_page = $page;
    }
	function setup() { }
	function breadcrumb(&$breadcrumb) { }
	function title(&$title) { }
	function displayAccess() { }
	function beforePopulate() { }
	function populate() { }
	function afterPopulate() { }
	function beforeGenerate() { }
	function beforeInstall() { }
	function beforeAllGenerate() { }
	function generate() { }
	function afterGenerate() { }
	function afterAllGenerate() { }
	function menu($menu) { }
	function afterMenu($menu) { }
	function submitAccess($values) { }
	function beforeSubmit(&$values) { }
	function menuPath(&$menuPath) { }
	function menuAttrs(&$menuAttrs) { }
        function menuParams(&$menuParams) { }
	function submit($values) { }
	function validate($values) { }
	function requiredComponentList() { return array(); }
	function prepareStates() { }
	function getComponentList() {
		return $this->_componentList;
	}
	function setComponentList($componentList) {
		$this->_componentList = $componentList;
	}
	function isSetup() {
		return $this->_setup;
	}
	function setSetup($setup) {
		$this->_setup=$setup;
	}

	function expectedPermissions(&$expPerms) {}
	function afterSubmit(ADD_Page $submitPage) {}
	function menuLabel(&$menuLabel) {}
	function output() {}
	//function contentType(&$contentType) {}
	function baseContent() {}
	function viewFile(&$viewFile) {}
	function pathNotPage(&$pathNotPage) {}
	function menuIcon(&$menuIcon) {}
	function activeMenu(&$isActive, $currentPage, $currentSectionER) {}
	function getType() {
		$class = get_class($this);
		$matches = array();
		preg_match("/^ADD_Component_(.+)$/",$class,$matches);
		if (isset($matches[1])) {
			return $matches[1];
		}
		return $class;
	}
	function getSetting($settingName,$default=null) {
		return $this->_page->getSetting($this->getType().".".$settingName,$default);
	}
    function assertSetting($settingName) {
        return $this->_page->assertSetting($this->getType().".".$settingName);
    }
	function getAllSettings() {
		$compType = $this->getType();
		$pageSettings = $this->_page->getSettings();
		$compSettings = array();
		foreach($pageSettings as $name => $value) {
			$matches=array();
			if(preg_match("/^".preg_quote($compType,"/")."\.(.+)$/",$name,$matches)) {
				$compSettings[$matches[1]]=$value;
			}
		}
		return $compSettings;
	}
	function userLogin(ADD_AccessManager $accessManager) { }
	function userLogout(ADD_AccessManager $accessManager) { }
}

?>
