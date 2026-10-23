<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 01/03/2017
 * Time: 09:04
 */

namespace ClinicLE\Data\Util;


class DatabaseUtil
{
    public static function makePlaceholders($params) {
        $count = is_array($params) ? count($params) : $params;
        $pholderList = array_fill(0, $count, "?");
        return implode(",", $pholderList);
    }
}