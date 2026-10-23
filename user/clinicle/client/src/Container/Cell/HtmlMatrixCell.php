<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 11:26
 */
namespace ClinicLE\Client\Container\Cell;

use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Content\Render\HtmlElement;

class HtmlMatrixCell extends MatrixCell
{
    function getCell()
    {
        $cellBindHtml = "cell.label";

        $cell = "<span ng-bind-html=\"$cellBindHtml\"></span>";
        return $cell;
    }
}