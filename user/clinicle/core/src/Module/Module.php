<?php
namespace ClinicLE\Module;
use ADD_PageCollection;
use ADD;
use ClinicLE\Common\Exception\DirectoryNotFoundException;

class Module implements \ADD_Module {
	private $_name = null;
	private $_template = null;
	private $_pages = null;
	private $_init = false;
    private $_extends = null;
    private $_subModules = array();
    private $_path = null;
    private $_core = false;
	
	const BASE_PAGE="BASE";
	
	function __construct($modName, $configOrModPath) {
            $this->setName($modName);

            if (is_a($configOrModPath,"\ClinicLE\Common\Config\AbstractConfig")) {
                    $template = $configOrModPath;
            }
            else {
                    $modPath = $configOrModPath;
                    if (!is_dir($modPath)) {
                            throw new DirectoryNotFoundException("Cannot init module '$modName' as dir '$modPath' does not exist");
                    }

                    $tplFile = $modPath."/page.ini";

                    $template = new FileConfig($tplFile);
                    $this->_path = $modPath;
            }
			$this->_template=$template;
			$alias = $template->getSetting('alias');
			if(!$alias) {
				$modPathParts = explode('/',$modName);
				$alias = $modPathParts[count($modPathParts)-1];
			}
            $this->alias = $alias;
            $extModName = $template->getSetting('extends');
            
            
            $this->setExtends($extModName);
	}
	
    function isCore() {
		return $this->_core;
	}
	function setCore($core) {
		$this->_core = $core;
	}
	function setExtends($extends) {
        $this->_extends = $extends;
    }

    function getExtends() {
        return $this->_extends;
    }

	public function setInit($init) {
		$this->_init = $init;
	}
	public function isInit() {
		return $this->_init;
	}
	function initPages(ADD_PageCollection $pageCollection) {
		$moduleJavascript = $this->getSetting('javascript');
			if($moduleJavascript) {
				$moduleName = $this->_name;
				if(!is_array($moduleJavascript)) {
					$moduleJavascript = array($moduleJavascript);
				}
				$javascript = array_map(function($jsItem) use($moduleName) { return $moduleName."/".$jsItem; },$moduleJavascript);
				$this->getTemplate()->setSetting('javascript',$javascript);
				
			}
            $template = $this->getTemplate();
			$this->_pages = $pageCollection;
            
			$pageCollection->initialise($template, $this->getPath());
              
            $this->setInit(true);
	}

    public function initSubModules() { 
        $extModName=$this->getExtends();
		
		if(!$extModName) return;
		$add = ADD::sys();

        $extMod = $add->getModule($extModName);
		
		if (!$extMod) throw new Exception\ModuleUndefinedException("Module '{$this->getName()}' cannot extend module '$extModName' it does not exist");
        $extMod->addSubModule($this->getName());
    }
	
    function addSubModule($subModule) {
		 $this->_subModules[]=$subModule;
    }

	function getPages() {
            return $this->_pages;    
        }
        
	function getPagesInStructure() {
		$add = ADD::sys();
		$smod = $this->getSuperModule();
		$pageCollection = new ADD_PageCollection($this);
		$pageCollection->combineWith($smod->getPages());
		foreach($smod->_subModules as $subModName) {
			$subMod = $add->getModule($subModName);
			if(!$subMod) throw new Exception\ModuleUndefinedException("Sub-module '$subModName' does not exist. It could not be added to super-module '".$this->getName());
			$pageCollection->combineWith($subMod->getPages());
		}

		return $pageCollection;
	}

	function getPageInStructure($pageName) {
            $add = ADD::sys();
            $page = $this->_pages->get($pageName);
            if ($page) return $page;
            
            if ($this->getExtends()) {
                $page = $add->getModule($this->getExtends())->getPage($pageName,true);
                if ($page) return $page;
            }
            foreach($this->_subModules as $subModName) {
                $page = $add->getModule($subModName)->getPage($pageName,true);
                if ($page) return $page;
            }
            return null;
	}
        function getPage($pageName) {
            return $this->_pages->get($pageName);
        }
        
	function getName() {
		return $this->_name;
	}
	
	function setName($name) {
		$this->_name = $name;
	}
	public function getSetting($name,$default=null) {
        return $this->_template->getSetting($name,$default);
    }
    public function getSettings() {
        return $this->_template->getSettings();
    }
    function getSettingList($key,$sepChar=" ") {
        return $this->_template->getSettingList($key,$sepChar);
	}
	public function getTemplate() {
		return $this->_template;
	}
	
	public function setTemplate($template) {
		return $this->_template=$template;
	}
	
	function setPath($path) {
		$this->_path = $path;
	}
	
	function getPath() {
		return $this->_path;
	}
	
	function loadPage($pageName) {
		if (!$this->isInit()) {
			throw new Exception\ModuleInitialisationException("Module class has not been initialised");
		}
		$page = $this->getPage($pageName);
		$page->init();
		return $page;
	}
	
	function getTopPages() {
		$topPages =array();
		foreach($this->getPages() as $name=>$page) {
			if ($page->getSetting('parent')) continue;
			$topPages[]=$page;
		}
		return $topPages;
	}
	
	function getChildPages($parent) {
            $add = ADD::sys();
            $childPages =array();
            foreach($this->getPagesInStructure() as $name=>$page) {
                    if ($page->getSetting('parent')!=$parent->getName()) continue;
                    $childPages[]=$page;
            }
			
            foreach($this->_subModules as $subModName) {
                $subMod = $add->getModule($subModName);
                $childPages=$childPages + $subMod->getChildPages($parent);
            }
			return $childPages;
	}

	function hasAnyPermission() {
            foreach($this->getPages() as $name => $page) {
                    foreach($page->getPermissions() as $name => $hasPerm) {
                            if ($hasPerm) return true;
                    }
            }
	}
        
	function version() {
		$path = $this->getPath();

		if (is_file($path."/VERSION")) {
			$parts = preg_split('/\s+/', trim((string) file_get_contents($path."/VERSION")));
			return array($parts[0], isset($parts[1]) ? $parts[1] : "");
		}

		if (is_file($path."/composer.json")) {
			$config = json_decode((string) file_get_contents($path."/composer.json"), true);
			if (is_array($config) && !empty($config["version"])) {
				return array($config["version"], "");
			}
		}

		return array("?.?.?", "");
	}
	
    function getSubModules() {
        return $this->_subModules;
    }
    
    function getSuperModule() {
        $extends = $this->getExtends();
		if (!$extends) return $this;
        return ADD::sys()->getModule($extends);
    }
        
	function getAlias() {
		return $this->alias;
	}
}

?>
