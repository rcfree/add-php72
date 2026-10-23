<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 20/09/16
 * Time: 15:32
 */

namespace ClinicLE\Options\Event;


use ClinicLE\Options\Resource\HomeToolManager;
use Symfony\Component\EventDispatcher\Event;

class HomeToolEvent extends Event
{
    private $homeToolManager;

    /**
     * @return HomeToolManager
     */
    public function getHomeToolManager()
    {
        return $this->homeToolManager;
    }

    public function __construct(HomeToolManager $homeToolManager) {
        $this->homeToolManager = $homeToolManager;
    }
}