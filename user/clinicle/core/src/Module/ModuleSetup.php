<?php
namespace ClinicLE\Module;

use ClinicLE\System\System;
use ClinicLE\Common\Config\FileConfig;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Common\Config\AbstractConfig;
use Symfony\Component\Finder\Finder;
use ADD;
use ADD_Util;
use ADD_PageCollection;
use ADD_Page;

class ModuleSetup {
	protected $system;
	protected $moduleMap=array();
	protected $mapExtracted = false;
	
	function __construct(System $system) {
		$this->system = $system;
	}
	
	function extractBaseModuleName($subModuleName,$config) {
		$tempSmn = explode('/',$subModuleName);
		$baseModName = $config->getSetting("extends");
		if(!$baseModName) {
			return null;
		}
		$tempBmn = explode('/',$baseModName);
		if(count($tempBmn)==1) {
			$baseModName = $tempSmn[0]."/".$baseModName;
		}
		return $baseModName;
	}
	function loadModules() {
		$moduleMap = $this->extractModuleMap($this->system->getWorkModulePath());
		foreach($moduleMap as $baseModName => $baseModDetails) {
			$this->addSingleModuleToSystem($baseModName,$baseModDetails);
		}
	}
	
	function loadAllModulesFromConfig($inPlace=false) {
		$this->loadModules();
	}
	protected function addSingleModuleToSystem($moduleName, $moduleDetails) {
		$baseModule = $this->initBaseModule($moduleName, $moduleDetails);
		$this->system->getModules()->add($baseModule);
	}
	
	function loadAllResources() {
		$moduleMap = $this->extractModuleMap($this->system->getWorkModulePath());
		foreach($moduleMap as $baseModName => $baseModDetails) {
			$baseModTemplate = ADD_Util::val("config",$baseModDetails,null);
			if(!$baseModTemplate) {
				throw new \Exception("No module config for module ".$baseModName.print_r($baseModDetails,1));
			}
			$this->addNamespaceToSystem($baseModTemplate);
			$this->addResourcesToSystem($baseModTemplate);
			
			$subModuleNames = ADD_Util::val("sub_modules",$baseModDetails,array());
			foreach($subModuleNames as $subModuleName => $subModuleTemplate) {
				$this->addNamespaceToSystem($subModuleTemplate);

				$this->addResourcesToSystem($subModuleTemplate);
			}
			
		}
	}
	/*
	function loadSingleModuleWithDependencies($moduleName, $modulePath, $depPath) {
		$this->loadAllResources();
		$moduleFile = $modulePath."/".$moduleName."/page.ini";
		$singleConfig = is_file($moduleFile) === true ? new FileConfig($moduleFile,true) : new ArrayConfig(array(),true);
		
		$singleMod = $this->initModule($moduleName, $singleConfig, $modulePath."/".$moduleName);
		
		$extends = $singleMod->getSetting("extends");
		if(!$extends) {
			$singleMod->initPages(new ADD_PageCollection($singleMod));
			$this->system->getModules()->add($singleMod);
			$this->addNamespaceToSystem($singleConfig);
			$this->addResourcesToSystem($singleConfig);
			return;
		}
		
		$baseModule = ADD::sys()->getModule($extends);
		$basePages = $baseModule->getPages();
		
		$singleMod->initPages($basePages);
		
		$this->combineModules($baseModule,$singleMod);
		
		$subMod = $this->initModule($moduleName, $singleConfig, $modulePath."/".$moduleName);
		
		$subMod->initPages(new ADD_PageCollection($subMod));
		$this->system->getModules()->add($subMod);
		$this->addNamespaceToSystem($singleConfig);
			
		$this->addResourcesToSystem($singleConfig);
		
	}
	*/
	function _loadDependencies(Module $module, $depPath) {
		$ml = new ModuleLoad($depPath);
		
		$compConfig = json_decode(file_get_contents($module->getPath()."/composer.json"),true);
		$requireMods = ADD_Util::val("require",$compConfig);
		
		$depModNames = array();
		if($module->getExtends()) {
			if(!$this->system->getModule($module->getExtends())) {
				$depModNames[]=$module->getExtends();
			}
		}
		if($requireMods) {
			foreach($requireMods as $modName => $modVersion) {
				if($this->system->getModule($modName)) continue;
				if(!file_exists($depPath."/".$modName)) continue;
				
				$depModNames[]=$modName;
			}
		}
		
		foreach($depModNames as $depModName) {
			$ml->loadSingle($this->system->getModules(),$depModName,false,false);
		}
	}
	function initBaseModule($baseModName, $baseModDetails) {

		$baseModTemplate = ADD_Util::val("config",$baseModDetails,null);
		$baseModPath = ADD_Util::val("path",$baseModDetails);

		if ($baseModTemplate) {
			$this->addNamespaceToSystem($baseModTemplate);
			$this->addResourcesToSystem($baseModTemplate);
		}
		$baseModule = $this->initModule($baseModName,$baseModTemplate, $baseModPath);
		$basePages = new ADD_PageCollection($baseModule);
		$baseModule->initPages($basePages);
		
		$subModuleNames = ADD_Util::val("sub_modules",$baseModDetails,array());
		foreach($subModuleNames as $subModuleName => $subModuleTemplate) {
			$this->addSubModuleToBaseModule($subModuleName, $subModuleTemplate, $baseModule);
		}
		return $baseModule;
	}
	function addSubModuleToBaseModule($subModuleName, $subModuleTemplate, $baseModule) {
		$this->addNamespaceToSystem($subModuleTemplate);

		$this->addResourcesToSystem($subModuleTemplate);

		$subModule = $this->initModule($subModuleName, $subModuleTemplate, $this->system->getWorkModulePath($subModuleName));
		$subModule->initPages($baseModule->getPages());
		$this->combineModules($baseModule,$subModule);
	}
	function addNamespaceToSystem(AbstractConfig $config) {
		
		if(!$config) {
			throw new \Exception();
		}
		$namespaces = $config->getSetting("namespace");
			
		if(!$namespaces) {
			return;
		}
		if(!is_array($namespaces)) {
			$namespaces = array($namespaces);
		}
		foreach($namespaces as $namespace) {
			ADD::sys()->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY)->addNamespace($namespace);
		}
	}
	function addResourcesToSystem(AbstractConfig $config) {
		$resourceTypes = $config->getSetting("resources");
		if(!$resourceTypes) {
			return;
		}
		$add = ADD::sys();
		if(!is_array($resourceTypes)) {
			$resourceTypes = array($resourceTypes);
		}
		$factory = $add->getFactory(\ClinicLE\System\Factory\SystemFactories::RESOURCE);
		foreach($resourceTypes as $resourceType) {
			$resource = $factory->newInstance($resourceType);
			$resource->initResource($add->getServiceContainer(),$add->getEventDispatcher());
		}
	}
	
	function combineModules($baseModule,$subModule) {
		
		$baseTemplate = $baseModule->getTemplate();
		$subTemplate = $subModule->getTemplate();
		foreach($subTemplate->getSettings() as $settingName => $settingValue) {
				
			if(in_array($settingName,array("name","alias","title","requires","uses","extends"))) {
				continue;
			}
            $currSetting = $baseTemplate->getSetting($settingName);
			
            if(!$currSetting) {
                $baseTemplate->setSetting($settingName,$settingValue);
                continue;
            }
			
			if(!is_array($currSetting)) {
                $currSetting=array($currSetting);
            }
			if(is_array($settingValue)) {
				foreach($settingValue as $settingValueItem) {
					$currSetting[]=$settingValueItem;
				}
			}
			
            $baseTemplate->setSetting($settingName,$currSetting);
		}
		
	}
	
	function initModule($moduleName,$modTemplate, $modulePath) {
		$mod = new Module($moduleName,$modTemplate);
		$mod->setPath($modulePath);
		return $mod;
	}
	
	function extractModuleMap($modulePath) {
		if($this->mapExtracted) {
			return $this->moduleMap;
		}
		$finder = new Finder();
		$moduleDirs = $finder->directories()->depth('==1')->in($modulePath);
		
		foreach($moduleDirs as $moduleDir) {
			$singleModPath = $moduleDir->getPathName();
			$this->addSingleModuleToMap($singleModPath);
		}
		foreach($this->moduleMap as $moduleName=>$moduleItem) {
			if(!isset($moduleItem['config'])) {
				unset($this->moduleMap[$moduleName]);
			}
		}
		$this->mapExtracted = true;
		return $this->moduleMap;
	}
	protected function addSingleModuleToMap($modulePath) {
		$modConfigFile = $modulePath."/page.ini";
			
		preg_match('/^\/.+\/(.+\/.+)??$/',$modulePath,$matches);
		$modName = $matches[1];
		
		if(!file_exists($modConfigFile)) {
			$this->moduleMap[$modName]=array('config'=>new ArrayConfig(array()),'sub_modules'=>array(),'path'=>$modulePath);
			return;
		}
		
		$singleConfig = new FileConfig($modConfigFile,true);
			
		$extends = $this->extractBaseModuleName($modName,$singleConfig);
		if($extends) {
			$this->dealWithSubModule($extends, $modName, $modulePath, $singleConfig);
			return;
		}
		$this->dealWithBaseModule($modName, $modulePath, $singleConfig);
	}
	function dealWithBaseModule($moduleName, $modulePath, $moduleConfig) {
		if(!isset($this->moduleMap[$moduleName])) {
			$this->moduleMap[$moduleName] = array();
		}
		$this->moduleMap[$moduleName]["path"]=$modulePath;
		$this->moduleMap[$moduleName]['config']=$moduleConfig;
	}
	function dealWithSubModule($extendedModuleName, $subModuleName, $modulePath, $subModuleConfig) {
		if(!isset($this->moduleMap[$extendedModuleName])) {
			$this->moduleMap[$extendedModuleName] = array("sub_modules"=>array());
		}
		$this->moduleMap[$extendedModuleName]['sub_modules'][$subModuleName]=$subModuleConfig;
	}
}
