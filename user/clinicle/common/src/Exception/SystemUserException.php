<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/03/2017
 * Time: 10:08
 */

namespace ClinicLE\Common\Exception;


class SystemUserException extends AbstractUserException
{
    function getUserMessage() {
        return "A system configuration error occured";
    }
    function isSystemAdminError() {
        return true;
    }
}