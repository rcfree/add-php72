<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/11/16
 * Time: 13:26
 */

namespace ClinicLE\Client\Container\Cell;


use ClinicLE\Client\Container\Table;

class RowActionCell extends AbstractCell
{
    private $rowAction;
    function __construct($rowAction) {
        $this->rowAction = $rowAction;
    }

    function getCell()
    {
        return $this->rowAction->outputMarkup();
    }

    function getHeading()
    {
        return $this->rowAction->getColumnHeading();
    }
    function afterTable(Table $table)
    {
        $html = new \ADD_Content_Html();
        $this->rowAction->afterOutputMarkup($html);
        return $html;
    }
}