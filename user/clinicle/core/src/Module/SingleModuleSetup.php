<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InstallModuleSetup
 *
 * @author rob
 */
namespace ClinicLE\Module;
use ADD_PageCollection;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Common\Config\FileConfig;
use ADD;
use ClinicLE\Common\Util;

class SingleModuleSetup extends ModuleSetup {
	private $singleModuleName;
	private $singleModulePath;
	private $ignoreSubModule = false;
	
	function setIgnoreSubModule($ignoreSubModule) {
		$this->ignoreSubModule = $ignoreSubModule;
	}

	function setSingleModuleName($singleModuleName) {
		$this->singleModuleName = $singleModuleName;
	}
	
	function extractModuleMap($modulePath) {
		parent::extractModuleMap($modulePath);
		if(!$this->singleModuleName) {
			return $this->moduleMap;
		}

		$this->addSingleModuleToMap($this->singleModulePath."/".$this->singleModuleName);

        $moduleMap = $this->moduleMap;
        $baseModuleName = $this->findBaseModuleName($this->singleModuleName,$moduleMap);

        if ($baseModuleName) {
            $moduleMap[$this->singleModuleName] = $this->extractSubModulePages($this->singleModuleName,$moduleMap[$baseModuleName]);
        }
        else {
            $moduleMap[$this->singleModuleName]['sub_modules'] = array();
        }

        return $moduleMap;
	}

	function findBaseModuleName($subModuleName, $moduleMap) {
	    foreach($moduleMap as $baseModuleName => $moduleConfig) {
	        $subModuleMap = Util::val("sub_modules",$moduleConfig,array());
	        if (in_array($subModuleName,array_keys($subModuleMap))) {
	            return $baseModuleName;
            }
        }
        return null;
    }
    private function extractSubModulePages($subModuleNameToInclude, $baseModuleConfig) {
        $subModuleConfig = $baseModuleConfig['sub_modules'][$subModuleNameToInclude];
        $subModulePageNames = array_keys($subModuleConfig->getSections());

        $baseModuleConfig['sub_modules'] = array(
            $subModuleNameToInclude => $baseModuleConfig['sub_modules'][$subModuleNameToInclude]
        );

        $newConfig = array();
        foreach($baseModuleConfig['config']->getSections() as $sectionName => $section) {
            if (is_array($section)) {
                $newConfig[$sectionName]=$section;
            }
        }
        $baseModuleConfig['config']=new ArrayConfig($newConfig);
        return $baseModuleConfig;
    }
	function setSingleModulePath($singleModulePath) {
		$this->singleModulePath = $singleModulePath;
	}
	function addSubModuleToBaseModule($subModuleName, $subModuleTemplate, $baseModule) {
	    $this->addNamespaceToSystem($subModuleTemplate);

		$this->addResourcesToSystem($subModuleTemplate);

		$subModule = $this->initModule($subModuleName, $subModuleTemplate, $this->system->getSourceModulePath($subModuleName));
		$subModule->initPages($baseModule->getPages());
		$this->combineModules($baseModule,$subModule);
	}
	function dealWithSubModule($extendedModuleName, $subModuleName, $subModulePath, $subModuleConfig) {
		if($this->ignoreSubModule) {
			$this->dealWithBaseModule($subModuleName, $this->singleModulePath."/".$subModuleName, $subModuleConfig);
		}
		if(!isset($this->moduleMap[$extendedModuleName])) {
			$this->moduleMap[$extendedModuleName] = array("sub_modules"=>array());
		}
		$this->moduleMap[$extendedModuleName]['sub_modules'][$subModuleName]=$subModuleConfig;
	}
	
}
