<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Options\Resource;
use CLE;
use ClinicLE\Module\AbstractResource;
use ClinicLE\Options\Event\HomeToolEvent;
use ClinicLE\Options\Event\HomeToolEvents;
use ClinicLE\System\Event\EventDispatcher;
use Pimple\Container;

/**
 * Description of OptionResource
 *
 * @author rcf8
 */
class OptionsResource extends AbstractResource {
	function initResource(Container $services, EventDispatcher $events) {
		$services[\Clinicle\System\Service\CoreServices::FACTORY_REGISTRY]
				->addFactory(OptionsFactories::HOMETOOL, "ClinicLE\Options\Resource\HomeToolFactory");
        $services[OptionsServices::HOMETOOL_MANAGER]=function($c) {
          return new HomeToolManager();
        };
        $events->addListener(HomeToolEvents::BEFORE_RENDER,array($this,"onBeforeRender"));
	}
	function onBeforeRender(HomeToolEvent $event) {
	    $event->getHomeToolManager()->populateHometools(CLE::sys());
    }
}
