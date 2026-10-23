<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/11/16
 * Time: 15:01
 */

namespace ClinicLE\Client\Container\Cell;


class MatrixCell extends AbstractCell
{
    private $headingColumnWidth;
    private $dataColumnWidth;
    private $cellActions;
    function __construct($headingColumnWidth, $dataColumnWidth, $cellActions = array()) {
        $this->headingColumnWidth = $headingColumnWidth;
        $this->dataColumnWidth = $dataColumnWidth;
        $this->cellActions = $cellActions;

    }
    function getCell()
    {
        $cellBindHtml = "cell.head ? cell.label : renderCell(cell)";

        $cell = "";
        foreach($this->cellActions as $cellAction) {
            $cell.="&nbsp;".$cellAction->outputMarkup();
        }
        $cell = "<span ng-bind-html=\"$cellBindHtml\"></span>$cell";
        return $cell;
    }
    function getHeading() {
        return null;
    }
    function getAttrs() {
        return array("ng-cloak"=>"","ng-repeat"=>"cell in row track by \$index","style"=>"{{cell.head ? 'width:".$this->headingColumnWidth."' : 'width:".$this->dataColumnWidth."'}}","class"=>"{{cell
        .head ? 
            'table-heading' : '' }}");
    }
}