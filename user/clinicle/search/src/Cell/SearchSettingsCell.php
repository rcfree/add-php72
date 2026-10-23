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

class SearchSettingsCell extends \ClinicLE\Client\Container\Cell\ExpressionCell
{
    function __construct() {
        $html = new HtmlElement();
        $html->addTextElement("button","Change settings..",array("class"=>"btn btn-mini btn-default","ng-click"=>"changeSettings(row.id)"));
        $html->startElement("dl",array("class"=>"dl-horizontal"));
        $html->addTextElement("span","<dt>{{setting}}</dt><dd>{{value}}</dd>",array("ng-repeat"=>"(setting,value) in row.settings"));
        $html->endElement("dl");
        $this->setFieldExpression($html);
    }
    function getHeading() {
        return "Settings";
    }
}