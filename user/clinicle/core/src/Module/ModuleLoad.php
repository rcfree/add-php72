<?php
namespace ClinicLE\Module;

class ModuleLoad {
    private $_modPath = null;
    private $_core;
    function __construct($modPath, $core=false) {
        if (!is_dir($modPath)) throw new ADD_SystemException("Specified module path '$modPath' does not exist");
        $this->_modPath = $modPath;
		$this->_core = $core;
    }
    function load(ADD_ModuleCollection $modules,$modNames=null, $excludeExt=false, $noInit=false) {
        $modPath = $this->_modPath;
        if (!$modNames) $modNames = $this->getModulesInPath();
        //$this->addIncludePaths($modNames);
        
        foreach($modNames as $modName) {
			$modTemplate = "$modPath/$modName";
            if (!is_dir($modTemplate)) continue;
            if ($modules->get($modName)) continue;
            $mod = new ADD_Module($modName,$modTemplate);
            
            if (!$excludeExt && $mod->getExtends()) {
                $this->load($modules,array($mod->getExtends()));
            }
            
            if(!$noInit) $mod->initPages(new ADD_PageCollection($mod));
            
            $mod->setCore($this->_core);
            $modules->add($mod);
        }
		foreach($modNames as $modName) {
			
			$mod = $modules->get($modName);
			if (!$mod) continue;
			if (!$excludeExt) $mod->initSubModules();
		}
        ADD::log()->debug("Loaded modules ".json_encode($modNames)." from ".$modPath);
    }
	
	function loadSingle(ADD_ModuleCollection $modules,$modName, $excludeExt=false, $noInit=false) {
		$this->load($modules,array($modName),$excludeExt, $noInit);
		return $modules->get($modName);
    }

    function addIncludePaths($modNames=null) {
        $modPath = $this->_modPath;
        $modPhpDirs=array();
        if (!$modNames) $modNames = $this->getModulesInPath();
        $incPaths = explode(":",get_include_path());
        foreach($modNames as $modName) {

            if (file_exists($modPath."/$modName/php") && !isset($ips[$modPath."/$modName/php"])) {
                $modPhpDirs[] = $modPath."/$modName/php";
            }
            if (file_exists($modPath."/$modName/lib")) {
                $modPhpDirs[] = $modPath."/$modName/lib";
            }
        }
	set_include_path(get_include_path().":".implode(":",$modPhpDirs));
    }
    function removeIncludePaths($modNames=null) {
        $modPath = $this->_modPath;
        $modPhpDirs=array();
        if (!$modNames) $modNames = $this->getModulesInPath();
        $incPaths = explode(":",get_include_path());
        foreach($modNames as $modName) {
            $findPos = null;
            if (file_exists($modPath."/$modName/php") && !isset($ips[$modPath."/$modName/php"])) {
                $findPos = array_search($modPath."/$modName/php",$incPaths);
                
            }
            if (file_exists($modPath."/$modName/lib")) {
                $findPos = array_search($modPath."/$modName/lib",$incPaths);
            }
            if($findPos) {
                unset($incPaths[$findPos]);
            }
        }
	set_include_path(implode(":",$incPaths));
    }

    public function getModulesInPath() {
           $dirNames = scandir($this->_modPath);
		   ADD::log()->info("dirNames:".json_encode($dirNames));
           $modNames = array();
           foreach($dirNames as $dirName) {
                   $dirNamePath = $this->_modPath."/$dirName"; 
                   if (!file_exists($dirNamePath)) throw new ADD_SystemException("Cannot load module '$dirNamePath'");
                   if (!is_dir($dirNamePath)) continue;
                   if (preg_match('/^\./',$dirName) || $dirName=='.' || $dirName=='..' || $dirName=="images") continue;
                   $subModNames = scandir($dirNamePath);
                   $modNames=array_merge($modNames,array_map(function($subModName)use($dirName) { return $dirName."/".$subModName; },$subModNames));
           }
		   ADD::log()->info("getModulesInPath:".json_encode($modNames));
           return $modNames;
     }
	
	public function getModPath() {
		return $this->_modPath;
	}
        
        public function exists($modName) {
            $dirNamePath = $this->_modPath."/$modName";
            if (!file_exists($dirNamePath)) return false;
            return true;
        }
            
}

