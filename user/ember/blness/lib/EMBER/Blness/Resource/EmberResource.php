<?php
namespace EMBER\Blness\Resource;
use ADD\Pathology\Resource\TrialSupport;
use ClinicLE\Module\AbstractResource;
use ClinicLE\System\Event\EventDispatcher;
use Pimple\Container;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 12:17
 */
class EmberResource extends AbstractResource
{
    function initResource(Container $services, EventDispatcher $events)
    {
        $pathologyTrialSupport = new TrialSupport("T1667","blness-admission-demog","study_number");
        $events->addSubscriber($pathologyTrialSupport);
    }
}