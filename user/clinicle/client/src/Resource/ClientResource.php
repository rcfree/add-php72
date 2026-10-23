<?php
namespace ClinicLE\Client\Resource;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;

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
class ClientResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY]->addFactory(\ClinicLE\Client\Factory\ClientFactories::GRID_ACTION, "ClinicLE\Client\Factory\GridActionFactory");
        $services[\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY]->addFactory
        (\ClinicLE\Client\Factory\ClientFactories::PANEL, "ClinicLE\Client\Factory\PanelFactory");
	}
}
