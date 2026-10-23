<?php
namespace ClinicLE\Install;

use ADD;
use ADD_Util;
use ClinicLE\Install\Event\DataHandlerEvent;
use ClinicLE\Install\Event\InstallEvents;
/**
 * Loads forms into the database defined in the module bundle
 *
 * @author Rob Free
 * @category ADD
 * @package ModuleBundle
 */
class FormInstaller {
	function update($mod, $forceUpdateData, $deleteMissing=false) {
        foreach ($mod->getPages()->asArray() as $name=> $page) {
            try {
				ADD::log()->info("Install page '".$page->getName()."'");
				$this->_updatePage($page, $forceUpdateData, $deleteMissing);
            } catch (Exception $ex) {
                ADD::log($this)->error("Page '{$page->getName()}' (form = ".$page->getSetting('form').") INSTALL FAILED (" . $ex->getMessage() . "):\n" . $ex->getTraceAsString());
                throw $ex;
            }
        }
    }

    private function _updatePage($page,$forceUpdateData, $deleteMissing=false) {
        $add = ADD::sys();
        $log = ADD::log();
        $event = new Event\PageInstallEvent($page);
		$add->getEventDispatcher()->dispatch(InstallEvents::INSTALL_FORM,$event);
        
        if (!$page->getSetting('encounter'))
            return;
		
		if(!$page->getSetting('components') && !$page->getSetting('install')) return;
        
        if ($page->hasComponent('Form')) {
            $formComp = $page->getComponent('Form');
            if (get_class($formComp) == "ADD_Component_Form_File")
            return;
        }   
		
        $isForm = $page->getSetting('form');

        $dbEncForm = ADD::db('encounter')->getEncounterFormWithType($page->getSetting('encounter'));
        
        $isFile = false;
        if ($dbEncForm && !$isForm) {
                $form = ADD_Util::loadClass("ADD_Component_Form_DB");
                $isFile=false;
        }
        else {
                $form = ADD_Util::loadClass("ADD_Component_Form_File");
                $isFile=true;
        }
		//$log->info("Page ".$page->getName()." is ".get_class($form).", dbEncForm:".json_encode($dbEncForm).", isForm:$isForm");
 
		
        $form->setPage($page);
		
        if($isFile) $form->setNotFinalise(true);
        
        $page->setComponent('Form', $form);
        $form->setup();
        $form->beforeInstall();
        
        
        $formSync = new FormSync($form,$page);
		if(method_exists($formSync,"setDeleteMissing")) {
			$formSync->setDeleteMissing($deleteMissing);
		}
        $dataHandler = new DataHandler($formSync);

        $dataHandler->populateFormSync($page->getSetting('encounter'),$page);
        $event = new DataHandlerEvent($dataHandler,$formSync);

        if ($isForm) {
			/*
			if($page->getName()=="a2e") {
				$log->info("formSync:".print_r($formSync,1));
				exit;
			}
			 * 
			 */
	        $dataHandler->findChangedObservationFields($page);
            $add->getEvents()->dispatch(InstallEvents::AFTER_SYNC,$event);

            $missingFields = $formSync->checkMissingFields();

	        if(count($missingFields)>0) {
                throw new Exception\FieldDefinitionException(count($missingFields) . " fields defined in previous form revision are not present: ".implode(", ",$missingFields));
            }
	        $dataHandler->updateEncounterForm($formSync);
	        
	        $fieldStore = new FieldStore($formSync);
			$fieldStore->storeFields();
            if(ADD::sys()->config("custom_tables")) {
                    $dataTable = new CustomTable($formSync,$page);
                    $dataTable->createOrUpdate();
            }
		}
		else {
                    $log->info("Page ".$page->getName()." is not form");
                }
                
                
                
        $ud = new DataUpdate($formSync);
		
	   	$ud->updateListObservationData();
		//$ud->updateExpressionData($forceUpdateData);
	
    }

}

?>