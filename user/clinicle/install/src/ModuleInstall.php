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
use Exception;
use ClinicLE\Install\Event\InstallEvents;

class ModuleInstall {
    private $dictionariesRefreshed=false;
    private $dataUpdated=false;
    private $dryRun=false;

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
    
}