<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 11/07/17
 * Time: 11:10
 */

namespace ClinicLE\Page\Event;

use ClinicLE\Security\Access;
use Symfony\Component\EventDispatcher\Event;

class PageAccessEvent extends Event
{
    private $access;
    function __construct(Access $access) {
        $this->access = $access;
    }

    /**
     * @return Access
     */
    public function getAccess()
    {
        return $this->access;
    }

}