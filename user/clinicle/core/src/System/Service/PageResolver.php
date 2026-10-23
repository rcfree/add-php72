<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\System\Service;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\System\ModuleStructure;

class PageResolver {
    private $moduleStructure;
    private $defaultPath;
    private $openMenu;
    
    function __construct(ModuleStructure $moduleStructure, $defaultPath) {
	$this->moduleStructure = $moduleStructure;
	$this->defaultPath = $defaultPath;
    }
    
    function resolveFromRequest(Request $request) {
	$urlPath = $request->getPathInfo();
	echo "urlPath:$urlPath\n";
        if (substr($urlPath, 0, 1) == '/') {
            $urlPath = substr($urlPath, 1);
	}
        if (strlen($urlPath) == 0) {
            $urlPath = $this->defaultPath;
	}
        $pagesInMods = $this->moduleStructure->getAllPages();
        foreach ($pagesInMods as $moduleName => $pages) {
            foreach ($pages as $name => $page) {
                if ($urlPath !== $page->createPagePath()) continue;
		$module = $this->moduleStructure->getModule($moduleName);
		$superMod = $this->moduleStructure->getSuperModule($module);
		$this->openMenu = $superMod->getName();
		return $page;
            }
        }
	return null;
    }
    
    function getOpenMenu() {
	return $this->openMenu;
    }
}