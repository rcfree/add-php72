<?php
namespace ClinicLE\Install\Resource;
use Pimple\Container;
use ClinicLE\System\EventDispatcher\EventDispatcher;
use ClinicLE\Module\AbstractResource;
use ClinicLE\Migrate\MigrationHandler;
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
class InstallResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[InstallServices::MIGRATION_HANDLER]=function($c) {
            return new MigrationHandler($c[CoreServices::MAIN_CONNECTION]);
		};
	}
}
