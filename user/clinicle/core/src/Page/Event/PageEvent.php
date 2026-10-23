<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 11/07/17
 * Time: 11:10
 */

namespace ClinicLE\Page\Event;

use ClinicLE\Page\AbstractPage;
use ClinicLE\Security\Access;
use Symfony\Component\EventDispatcher\Event;

class PageEvent extends Event
{
    private $access;
    function __construct(AbstractPage $page) {
        $this->page = $page;
    }

    /**
     * @return AbstractPage
     */
    public function getPage()
    {
        return $this->page;
    }

}