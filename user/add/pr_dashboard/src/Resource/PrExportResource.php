<?php
namespace ADD\PR\Resource;
use ClinicLE\Module\AbstractResource;
use ClinicLE\Search\Event\ExportEvent;
use ClinicLE\Search\Event\SearchEvents;
use ClinicLE\System\Event\EventDispatcher;
use Pimple\Container;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 06/11/17
 * Time: 10:14
 */

class PrExportResource extends AbstractResource
{
    function initResource(Container $services, EventDispatcher $events)
    {
        $events->addListener(SearchEvents::BEFORE_OUTPUT,array($this,"onBeforeOutput"));
    }

    function onBeforeOutput(ExportEvent $event) {
        $dataFrame = $event->getDataFrame();
        $columns = $dataFrame->getColumns();
        $newColumns = preg_replace("/^pr\.prog\./","",$columns);
        $dataFrame->setColumns($newColumns);
    }
}