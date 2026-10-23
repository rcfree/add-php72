<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:26
 */

namespace ADD\PR;


use ClinicLE\Client\Container\LegendTable;
use ClinicLE\Client\Container\Table;

class SearchTable extends LegendTable
{
    function getAttrs() {
        return array("ng-show"=>$this->getDataSetName().".length>1");
    }




}