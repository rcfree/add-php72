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

class SeparateModuleSetup extends ModuleSetup {
	
	function dealWithSubModule($extendedModuleName, $subModuleName, $modulePath, $subModuleConfig) {
		$this->moduleMap[$subModuleName]=array("path"=>$modulePath, "config"=>$subModuleConfig);
	}
}
