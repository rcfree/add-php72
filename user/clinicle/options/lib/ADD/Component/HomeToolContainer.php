<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/09/16
 * Time: 15:46
 */
class ADD_Component_HomeToolContainer extends ADD_Component
{
    function init()
    {
        CLE::sys()->getEventDispatcher()->addListener(\ClinicLE\Options\Event\HomeToolEvents::BEFORE_RENDER, array
        ($this, "onBeforeRender"));
    }
    function onBeforeRender(\ClinicLE\Options\Event\HomeToolEvent $event) {
        $htm = $event->getHomeToolManager();
        foreach(explode(" ",$this->getSetting("hometools")) as $hometoolType) {
            $hometool = $htm->createHometool($hometoolType, CLE::sys());
            $hometool->setPage($this->getPage());
        }
    }
}