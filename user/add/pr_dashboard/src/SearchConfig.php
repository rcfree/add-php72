<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 21:39
 */

namespace ADD\PR;


use ClinicLE\Common\Config\FileConfig;

class SearchConfig extends FileConfig
{
    function __construct($modulePath) {
        $searchConfFile = $modulePath."/searches/searches.ini";
        parent::__construct($searchConfFile);
    }
}