<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 16:30
 */

namespace ADD\ResphyImport\Resource;


use ADD\ResphyImport\Command\ResphyImportCommand;
use ClinicLE\Module\AbstractResource;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Service\CoreServices;
use Pimple\Container;

class ResphyImportResource extends AbstractResource
{
    function initResource(Container $services, EventDispatcher $events)
    {
        $services[CoreServices::COMMAND_APP]->addCommands(
            array(
                new ResphyImportCommand()
            )
        );
    }

}