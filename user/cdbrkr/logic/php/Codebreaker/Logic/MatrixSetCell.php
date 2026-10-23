<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 28/11/16
 * Time: 09:34
 */

namespace Codebreaker\Logic;


use ClinicLE\Client\Container\Cell\AbstractCell;
use ClinicLE\Client\Util\AngularJS;

class MatrixSetCell extends AbstractCell
{
    private $columnCount;
    private $cellPattern;

    function __construct($columnCount,  $cellPattern) {
        $this->columnCount = $columnCount;
        $this->cellPattern = $cellPattern;
    }
    function getCell() {
        $buttonTpl = new \ADD_Content_Html();
        $buttonTpl->startElement("button",array(
            "style"=>'width:100%;height:64px;overflow:hidden;',
            "class"=>'btn btn-click ellipsis-container matrix-set-btn',
            "ng-class"=>"cell.is_primary == 1 ? 'btn-danger' : cell.is_selected ? 'btn-success' : ''",
            "type"=>'button',
            "ng-click"=>"toggle(cell.code, cell.selected_id)",
            "title"=>'{{cell.description}}'
        ));
        $buttonTpl->addTextElement("span","{{".$this->cellPattern."}}",array("class"=>"ellipsis"));
        $buttonTpl->endElement("button");
        return $buttonTpl->output();
    }
    function getAttrs() {
        return array("ng-cloak"=>"","ng-repeat"=>"cell in row","style"=>"width:".intval(100/$this->columnCount)."%");
    }
}