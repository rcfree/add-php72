<?php
namespace ClinicLE\Manage\Resource;
use ClinicLE\Manage\Command\CleanupCommand;
use ClinicLE\Manage\Command\DbRestoreCommand;
use ClinicLE\Manage\Command\DevStatCommand;
use ClinicLE\Manage\Command\UnitTestCommand;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\Module\AbstractResource;
use ClinicLE\Migrate\MigrationHandler;
use ClinicLE\System\Service\CoreServices;
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
class ManageResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[ManageServices::MIGRATION_HANDLER]=function($c) {
            return new MigrationHandler($c[CoreServices::MAIN_CONNECTION]);
		};
		$services[CoreServices::FACTORY_REGISTRY]->addFactory(ManageFactories::COMMAND, "ClinicLE\Manage\Factory\CommandFactory");
	    $services[CoreServices::COMMAND_APP]->addCommands(array(
	        new DevStatCommand(),
            new UnitTestCommand(),
            new CleanupCommand(),
            new DbRestoreCommand()
        ));
	}
}
