<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Manage_Link extends ADD_Manage_AbstractManage {

    function getOptsList() {
        return "mr";
    }
    function getDescription() {
        return "Symlink module";
    }
	
    function manage($options) {
        $add = ADD::sys();
        
		
		$moduleDir = ADD_Util::val('m', $options) or die("FATAL: Module (-m) not specified\n");
			$sourceModulePath = $add->getSourceModulePath($moduleDir);
		
		$classFix = new ADD_Manage_ClassFix($this->getRunner()->getEnvironment()->getClassLoader());
			$mpr = new ADD_Manage_ModulePathResolver($sourceModulePath,$this->getRunner()->getEnvironment());
		$classFix->replaceClassMap($mpr);
		
		
		$targetSymLink = $mpr->getInstallPath();
                
		if(file_exists($targetSymLink)) {
			system("rm -r $targetSymLink");
		}
		ADD::log($this)->info("Link $sourceModulePath to $targetSymLink");
		system("ln -s $sourceModulePath $targetSymLink");
                
                chdir($add->getPath());
		
                ADD_Tool_Command::executeWithException("composer dump-autoload");
    }
}
