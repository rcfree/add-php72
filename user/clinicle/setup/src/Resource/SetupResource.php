<?php
namespace ClinicLE\Setup\Resource;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Module\AbstractResource;
use ClinicLE\Install\Event\InstallEvents;
use ClinicLE\Manage\Resource\ManageServices;
use CLE;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClientResource
 *
 * @author rob
 */

class SetupResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$events->addListener(InstallEvents::BEFORE_DICTIONARIES,array($this,"onBeforeDictionaries"));

	}

	function onBeforeDictionaries($event) {
		$migHandler = CLE::svc(ManageServices::MIGRATION_HANDLER);
		$setupModule = CLE::sys()->getModule("clinicle/setup");
		if(!$setupModule) {
			return;
		}
		$migHandler->migrateUsingFile($setupModule->getPath()."/sql/data.sql");
	}
}
