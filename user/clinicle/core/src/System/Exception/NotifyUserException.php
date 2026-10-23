<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/03/2017
 * Time: 10:08
 */

namespace ClinicLE\System\Exception;


use ClinicLE\Common\Exception\AbstractUserException;

class NotifyUserException extends AbstractUserException
{
    function getUserMessage() {
        return $this->getMessage();
    }
    function isSystemAdminError() {
        return false;
    }
}