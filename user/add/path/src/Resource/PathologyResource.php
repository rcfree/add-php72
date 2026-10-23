<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/09/16
 * Time: 12:09
 */

namespace ADD\Pathology\Resource;


use ClinicLE\Module\AbstractResource;
use Pimple\Container;
use ClinicLE\System\Event\EventDispatcher;
use CLE;
class PathologyResource extends AbstractResource
{
    function initResource(Container $services, EventDispatcher $events) {
        $services[PathologyServices::TRANSFER_ENGAGER] = function($c) {
            return new TransferEngager();
        };
        $services[PathologyServices::TRANSFER_STATUS] = function($c) {
            $config = CLE::sys()->getConfig()->getSection("Pathology");
            return new TransferStatus($config,CLE::sys()->getEvents());
        };
    }
}