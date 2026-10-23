<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 21/03/2017
 * Time: 10:08
 */

namespace ClinicLE\Common\Exception;


interface UserExceptionInterface
{
    function getUserMessage();
    function isSystemAdminError();
    function outputError();
    function getDebugTrace();
    function getExceptionClass();
    function getExceptionMessage();
}