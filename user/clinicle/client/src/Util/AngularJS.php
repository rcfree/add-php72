<?php

namespace ClinicLE\Client\Util;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 28/11/16
 * Time: 09:25
 */
class AngularJS
{
    static function createCustomCall($funcName, $args) {
        return "customCall('".$funcName."',[".implode(",",$args)."])";
    }
}