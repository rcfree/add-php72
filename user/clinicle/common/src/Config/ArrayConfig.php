<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common\Config;

class ArrayConfig extends AbstractConfig {
    function __construct(array $config, $ignoreArrays=false) {
        $this->_config = $config;
        $this->_parseConfig($ignoreArrays);
    }

}
