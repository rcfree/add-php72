<?php
namespace ClinicLE\Manage\Resource;
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
class AjaxResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[CoreServices::FACTORY_REGISTRY]->addFactory(AjaxFactories::AJAX_FUNCTION, "ClinicLE\Ajax\Resource\AjaxFunctionFactory");
	}
}
