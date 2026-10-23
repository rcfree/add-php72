<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 11:26
 */
namespace ClinicLE\Search\Cell;

use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Content\Render\HtmlElement;

class SearchAttributesCell extends \ClinicLE\Client\Container\Cell\ExpressionCell
{
    function __construct() {
        $html = new HtmlElement();
        $html->addTextElement("button","Edit attributes..",array("class"=>"btn btn-mini btn-default","ng-click"=>"editAttrs(row.id)"));
        $html->startElement("div");
        $html->addTextElement("span","<span class='label label-success' 
        >{{attr }}</span>&nbsp;",array("ng-repeat"=>"attr in row.attributes"));
        $html->endElement("div");
        $this->setFieldExpression($html);
    }
    function getHeading() {
        return "Attributes";
    }
}