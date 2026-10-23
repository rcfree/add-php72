<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/10/17
 * Time: 21:56
 */

namespace ClinicLE\Search\Util;


use ClinicLE\Common\Exception\FileNotFoundException;

class JsonFileLoader
{
    private $fileName;
    function __construct($fileName) {
        $this->fileName = $fileName;
    }
    function load() {
        if(!file_exists($this->fileName)) {
            throw new FileNotFoundException("File '".$this->fileName."' does not exist");
        }
        $searchJson = file_get_contents($this->fileName);
        return json_decode($searchJson, true);
    }
}