<?php
class ADD_Test_ModuleDependency {
	private $_module;
	private $_modules;
	function __construct(ADD_Module $module, ADD_ModuleCollection $modules) {
		$this->_module = $module;
		$this->_modules = $modules;
	}
	function getModule() {
		return $this->_module;
	}
	function getModules() {
		return $this->_modules;
	}
	
	function assertCorrectVersion($version,$type) {
		$log = ADD::log();
        
		$add = ADD::sys();
		$reqVer = $this->_module->getSetting('requires');
		
		if (!$reqVer) {
	    	$log->warn("Module '".$this->_module->getName()."' does not specify the required version of ADD (missing 'requires' setting in page.ini)");
			return;
		}
		
		$vc = new ADD_Tool_VersionCheck($reqVer);
		if(!$vc->isCompatibleWith($version)) {
	    	throw new ADD_ModuleDependencyException("Module '".$this->_module->getName()."' requires ADD $type $reqVer It is not compatible with ".$version);
		}
	}
	function _assertCorrectModuleVersion($moduleName,$required, $action) {
		$log = ADD::log();
       
		$vc = new ADD_Tool_VersionCheck($required);
		$module = $this->_modules->get($moduleName);
		list($version,$date)=$module->version();
		if(!$vc->isCompatibleWith($version)) {
			throw new Exception("$action module '$required' version is not compatible with '{$module->getName()}' (requires:{$required}, current:".$version.")");
		}
	}
	
	function assertModuleDependencies() {
        $log = ADD::log();
        $add = ADD::sys();
		
        $mod = $this->_module;
            
		$extends = $mod->getSetting('extends');
		$uses = $mod->getSetting('uses');
		
        $workMl = new ADD_ModuleLoad($add->getWorkModulePath());
                    /*
		if ($uses) {
            
            foreach($mod->getSettingList('uses',",") as $useModInfo) {
				$useModTemp = explode(" ",$useModInfo);
				if (!$workMl->exists($useModTemp[0])) {
                    throw new Exception("Module '".$useModTemp[0]."' used by '{$mod->getName()}' does not exist in {$workMl->getModPath()}");
                }
                $workMl->load($this->_modules,array($useModTemp[0]));
				
				if (isset($useModTemp[1])) {
					$this->_assertCorrectModuleVersion($useModTemp[0], $useModTemp[1],"Used");
				}
            }
		}
                
		if ($extends) {
			$extendsTemp = explode(" ",$extends);
				
            $log->debug("Extends module:".$extends);
            $extMod = $this->_modules->get($extendsTemp[0]);
            if (!$workMl->exists($extendsTemp[0])) {
                throw new Exception("Base extended module '$extends' could not be loaded from {$workMl->getModPath()}");
            }
            $workMl->load($this->_modules,array($extendsTemp[0]));
            
			if (isset($extendsTemp[1])) {
				$this->_assertCorrectModuleVersion($extendsTemp[0], $extendsTemp[1],"Base extended");
			}
        }
					 * 
					 */
    }
	
}