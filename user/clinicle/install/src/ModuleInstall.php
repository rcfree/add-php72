<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Install;
use ClinicLE\System\Service\CoreServices;
use ADD;
use ClinicLE\Module\Module;
use ClinicLE\System\Initialiser;
use ADD_Tool_Command;
use Exception;
use ClinicLE\Install\Event\InstallEvents;

class ModuleInstall {
    private $dictionariesRefreshed=false;
    private $dataUpdated=false;
    private $dryRun=false;
    private $installInline=false;
    
    public function isInstallInline() {
        return $this->installInline;
    }

    public function setInstallInline($installInline) {
        $this->installInline = $installInline;
    }

        public function isDryRun() {
        return $this->dryRun;
    }

    public function setDryRun($dryRun) {
        $this->dryRun = $dryRun;
    }

    function setDictionariesRefreshed($dictionariesRefreshed) {
        $this->dictionariesRefreshed = $dictionariesRefreshed;
    }
    function areDictionariesRefreshed() {
        return $this->dictionariesRefreshed;
    }
    function isDataUpdated() {
        return $this->dataUpdated;
    }
    function setDataUpdated($dataUpdated) {
        $this->dataUpdated = $dataUpdated;
    }
   
    function installForms(Module $module, $deleteMissing = false) {
        $log = ADD::log($this);
        $add = ADD::sys();
			
        $add->getService(CoreServices::FACTORY_REGISTRY)
                ->addFactory(InstallFactories::DICT_PLUGIN_FACTORY, "ClinicLE\Install\Factory\DictPluginFactory")
                ->addFactory(InstallFactories::DICT_TARGET_FACTORY, "ClinicLE\Install\Factory\DictTargetFactory");
        
		$events = $add->getEventDispatcher();
			
			
		try {
			$event = new Event\ModuleInstallEvent($module);
		
            $events->dispatch(InstallEvents::BEFORE_DICTIONARIES, $event);
		
			
            $dl = new Dictionary\Supervisor();
            
            $dl->update($module,$this->areDictionariesRefreshed());
            
			$events->dispatch(Event\InstallEvents::BEFORE_FORMS, $event);
			
            $fl = new FormInstaller();
            
            $fl->update($module, $this->isDataUpdated(), $deleteMissing);
			
				
            if($this->isDryRun()) {
                    $log->info("Not commited transaction (n flag present)");
                    ADD::db()->rollbackTxn();
            }
            else{
                    ADD::db()->commitTxn();
            }
            $events->dispatch(InstallEvents::AFTER_FORMS, $event);
			
        } 
        catch (Exception $ex) {
            ADD::db()->rollbackTxn();
            throw $ex;
        }
    }
    
    function installCode(Module $module) {
        $composerContent = file_get_contents($module->getPath()."/composer.json");
        $composerConfig = json_decode($composerContent,true);
        $modDir = $composerConfig['name'];

        $this->copyModule($module,$modDir,$this->isInstallInline());
        $this->createSymLinks($module, $modDir);
    }
    
    private function copyModule(Module $module,$modDir,$inline=false) {
        $add = ADD::sys();
        $log = ADD::log();
        
        $srcPath = $add->getSourceModulePath($module->getName());
        
        
        $tgtPath = $add->getWorkModulePath($modDir);
        
        $this->removeExistingModule($modDir,$tgtPath);
        
        if($inline) {
            $log->info("Symlinking '$modDir' module definition to $tgtPath");
            system("ln -s $srcPath $tgtPath");
        }
        else {
            $log->info("Copying '$modDir' module definition from $srcPath to $tgtPath");
            exec("cp -r $srcPath $tgtPath");
        }
		
        if (!file_exists($tgtPath)) {
            throw new Exception("Module '$$modDir' could not be copied to '$tgtPath' for an unknown reason");
        }
    }
    
    private function removeExistingModule($moduleName, $installPath) {
        $log = ADD::log($this);
        
        if (!file_exists($installPath)) {
            return;
        }
        $log->info("Removing existing '$moduleName' module definition from $installPath");
        
        exec("rm -r ". $installPath);
        if (file_exists($installPath)) {
            throw new Exception("Current module '$modName' could not be removed from '$installPath' for an unknown reason");
        }
    }
    public function createSymLinks($module, $modDir) {
        $log = ADD::log($this);
        if(!ADD::isInit()) {
            $init = new Initialiser(getenv('ADD_PATH'),getenv('ADD_PROFILE'));
            ADD::init($init);
        }
        
        $installPath = ADD::sys()->getPath()."/modules/live/".$modDir;
        
        $tgtFile = ADD::sys()->getPath()."/vendor/clinicle/web/public/module/".$modDir;
        $srcFile = "$installPath/web";
        if(file_exists($srcFile)) {
            ADD_Tool_Command::createSymLink($modDir,$srcFile,$tgtFile,"");
            $log->info("Create symlink from ".$srcFile." to $tgtFile\n");
        }
        $tgtFile = \ADD::sys()->getWebAppPath()."views/module/".$modDir;
        $srcFile = "$installPath/views";
        if(file_exists($srcFile)) {
            ADD_Tool_Command::createSymLink($modDir,$srcFile,$tgtFile,"");
            $log->info("Create symlink from ".$srcFile." to $tgtFile\n");
        }
    }
}