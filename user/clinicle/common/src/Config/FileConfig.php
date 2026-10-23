<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClinicLE\Common\Config;
use ClinicLE\Common\Exception\FileNotFoundException;

class FileConfig extends AbstractConfig {
    private $_fileName;
    function __construct($fileName,$ignoreArrays=false) {
        $this->_fileName = $fileName;
        $this->_ignoreArrays = $ignoreArrays;
        $this->_loadFile();
    }
    private function _loadFile() {
        if (!file_exists($this->_fileName)) {
	    throw new FileNotFoundException("Config file '{$this->_fileName}' not found");
	}
        $this->_fileName = $this->_fileName;
        $this->_config = parse_ini_file($this->_fileName,true);
        $this->_parseConfig();
        return true;
    }
}
