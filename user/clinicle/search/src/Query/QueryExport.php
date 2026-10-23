<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 30/01/17
 * Time: 13:16
 */

namespace ClinicLE\Search\Query;


use ClinicLE\Search\Event\ExportEvent;
use ClinicLE\Search\Event\SearchEvents;
use ClinicLE\System\Event\EventDispatcher;

class QueryExport
{

    function runExport(QueryRunner $runner, $dataWriter)
    {
        $dataFrame = $runner->runResults();
        $event = new ExportEvent($dataFrame);
        $runner->getEvents()->dispatch(SearchEvents::BEFORE_OUTPUT,$event);
        $dataWriter->write($dataFrame);
    }
}