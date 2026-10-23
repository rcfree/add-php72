<?php
/**
 * The Initialiser class is a factory for the System class which initialises the Application, system services (using Pimple)
 * the event dispatcher. It is passed an AbstractEnvironment which is used both during the initialisation and later.
 * 
 * An instance of Initialiser is passed to the static ADD::init() function which activates the system.
 * 
 * @package Main
 * @example $init = new ADD_Initialiser_FileBasedInitialiser("/local/add","add-test");
 * @example ADD::init($init)
 * @version 2.00
 * @since 14/03/2014
 * @author Rob Free
 */

namespace ClinicLE\System;

use ClinicLE\System\Event\SystemEvent;
use ClinicLE\System\Event\SystemEvents;
use Pimple\Container;
use ClinicLE\System\Service\CoreServiceProvider;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Environment\AbstractEnvironment;

class Initialiser {
    private $errorsHandled=false;
    
    /**
     * @param string $path
     * @param string $profile
     */
    public function __construct(AbstractEnvironment $environment) {
        $this->environment = $environment;
    }
	
    /**
     * @return System
     */
    public function createSystem() {
		$notices = new Notices();
		$application = new Application("Html",$notices);
        $serviceContainer = new Container();
        $eventDispatcher = new EventDispatcher();
		
		$modules = new \ClinicLE\Module\ModuleCollection();
		$moduleStructure = new ModuleStructure($modules);
        $system = new System($this->environment, $serviceContainer, $moduleStructure, $application, $eventDispatcher);
		$notices->setSystem($system);
		return $system;
    }
    
    public function areErrorsHandled() {
	return $this->errorsHandled;
    }
    public function setErrorsHandled($errorsHandled) {
	$this->errorsHandled = $errorsHandled;
    }
	public function initSystem(System $system) {
	    if($system->isInit()) {
			return;
		}
        $event = SystemEvent::createFromSystem($system);
        $events = $system->getEvents();
        $events->dispatch(SystemEvents::BEFORE_INIT,$event);
		$system->initServiceContainer();
		
		$system->initModules();
        $events->dispatch(SystemEvents::AFTER_INIT,$event);
        $system->setInit(true);
	}
}
