<?php
namespace ClinicLE\Client\Container\Cell;
use ClinicLE\Client\Container\Table;

/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:15
 */
abstract class AbstractCell
{
    private $heading;
    function __construct($heading) {
        $this->heading = $heading;
    }
    function init(Table $table) {

    }
    function getHeading() {
        return $this->heading;
    }
    abstract function getCell();

    function afterTable(Table $table) {

    }
    function getWidth() {
        return null;
    }
    function getAttrs() {
        return array();
    }
}