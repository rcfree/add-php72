<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Command;
use ClinicLE\Common\Util;

class ModulePathResolver {
	
    function __construct($sourceModulePath, $environment) {
		$this->sourceModulePath = $sourceModulePath;
		$this->environment = $environment;
	}
	function getInstallPath() {
		if(!file_exists($this->sourceModulePath."/composer.json")) throw new Exception("No composer.json file in ".$this->sourceModulePath);
        
        $composerContent = file_get_contents($this->sourceModulePath."/composer.json");
        $composerConfig = json_decode($composerContent,true);
        $moduleName = $composerConfig['name'];
        $type = Util::val('type',$composerConfig);

        if($type==="clinicle-module") {
                $targetPath=$this->environment->getPath()."/modules";
        }
        else {
                $targetPath=$this->environment->getPath()."/vendor";
        }
        $installPath = $targetPath."/".$moduleName;
        return $installPath;
    }
	
    function getSourcePath() {
            return $this->sourceModulePath;
    }
}
