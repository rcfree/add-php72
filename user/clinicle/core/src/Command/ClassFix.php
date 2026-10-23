<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace ClinicLE\Command;

use Composer\Autoload\ClassLoader;

class ClassFix {
	public function __construct(ClassLoader $classLoader) {
		$this->classLoader = $classLoader;
	}
	
	public function replaceClassMap($modulePathResolver) {
            $classMap = $this->classLoader->getClassMap();

            $replaceClassMap = array();

            $installPath = $modulePathResolver->getInstallPath();
            
            foreach($classMap as $className => $classFile) {
                
                if(strpos($classFile,$installPath)===false) {
                    continue;
                }

                $replaceClassFile=str_replace($modulePathResolver->getInstallPath(),$modulePathResolver->getSourcePath(),$classFile);
                $replaceClassMap[$className]=$replaceClassFile;
            }

            $this->classLoader->addClassMap($replaceClassMap);
	}
}
