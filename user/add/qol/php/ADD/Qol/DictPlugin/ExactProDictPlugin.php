<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 11/08/17
 * Time: 11:12
 */

namespace ADD\Qol\DictPlugin;


use ClinicLE\Install\Dictionary\DictPluginInterface;

class ExactProDictPlugin implements DictPluginInterface
{

    function processFields(&$fields)
    {
        // TODO: Implement processFields() method.
    }

    function beforeProcessRow(&$row)
    {
        $row['data']=$row['score'];
    }

    function afterProcessRow(&$row)
    {
        // TODO: Implement afterProcessRow() method.
    }
    function afterGetFields() {

    }
    function beforeImport() {

    }
}