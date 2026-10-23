<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 11:26
 */
namespace ClinicLE\Search\Cell;

use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Content\Render\HtmlElement;

class SearchResultCell extends MatrixCell
{
    function getCell()
    {
        $cellBindHtml = "cell.label";

        $cell = "<span ng-bind-html=\"$cellBindHtml | trustHtml \"></span>";
        return $cell;
    }
}