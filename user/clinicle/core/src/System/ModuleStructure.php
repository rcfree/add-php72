<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System;
use ClinicLE\Module\Module;
use ClinicLE\Module\ModuleCollection;

class ModuleStructure {

	public function __construct(ModuleCollection $modules) {
		$this->modules = $modules;
	}

	function getSuperModule(Module $module) {
		$extends = $this->getExtends();
		if (!$extends)
			return $this->module;
		return $modules->get($extends);
	}

	/**
	 * Utility method to return a specific page in a specific module
	 * @param string $modName
	 * @param string $pageName
	 * @return ClinicLE_Page
	 */
	function getPageInModule($modName, $pageName) {
		return $this->getModules()->getPageInModule($modName, $pageName);
	}

	/**
	 * Extracts all pages in all modules as an associative array
	 * @return array
	 */
	function getPagesInModules() {
		$pages = array();
		foreach ($this->getModules() as $modName => $module) {
			$pages[$modName] = array();
			foreach ($module->getPages() as $name => $page) {
				$pages[$modName][$name] = $page;
			}
		}
		return $pages;
	}

	function getModules() {
		return $this->modules;
	}

	function getModule($moduleName) {
		return $this->modules->get($moduleName);
	}
	
	function getPageFromPim($pim) {
		$pimTemp = explode(":",$pim);
		if(count($pimTemp)!=2) throw new \InvalidArgumentException("PIM ($pim) must have a module and page component");
		return $this->getPageInModule($pimTemp[0],$pimTemp[1]);
	 }
	function setModules($modules) {
		$this->modules=$modules;
	}
}
