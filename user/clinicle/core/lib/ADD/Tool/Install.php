<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ADD_Tool_Install {
	static function downloadModule($options,$modName) {
		if(!isset($options['d'])) {
			return;
		}
		$log = ADD::log();
		$downloadDir = tempnam("/tmp","add-install");
		unlink($downloadDir);
		mkdir($downloadDir);
		$log->info("Cloning default version of '".$modName."' module from ADD repo into '$downloadDir'");
		ADD_Tool_Command::executeWithException("hg clone https://bitbucket.org/leicrespbru/add_".$modName." ".$downloadDir." || exit 1",false,"Unable to clone module '$modName' from ADD repo");
		
		$sourceModPath = ADD::sys()->getSourceModulePath($modName);
		if (file_exists($sourceModPath)) {
			$prevPath = ADD::sys()->getSourceModulePath('previous');
			if(!file_exists($prevPath)) {
				mkdir($prevPath);
			}
			$timestampDir = $prevPath."/".$modName."-".date("dmYHis");
			$log->info("Moving existing '".$modName."' module into '$timestampDir'");
			ADD_Tool_Command::executeWithException("cp -r $sourceModPath $timestampDir || exit 1",false,"Unable to copy existing module '$modName' into '$timestampDir'");
			ADD_Tool_Command::executeWithException("rm -r $sourceModPath || exit 1",false,"Unable to remove existing module directory '$sourceModPath'");
		}
		$log->info("Moving downloaded '".$modName."' module into  '$sourceModPath'");
		rename($downloadDir,$sourceModPath);
	}
	
	static function createModuleSymLinks($modName,$tgtPath) {
		ADD_Tool_Command::createSymLink($modName,$tgtPath."/$modName/web", ADD::sys()->getWebAppPath()."module/".$modName,"web resources");
		ADD_Tool_Command::createSymLink($modName,$tgtPath."/$modName/views", ADD::sys()->getWebAppPath()."views/module/".$modName,"views resources");
	}
	
	
}