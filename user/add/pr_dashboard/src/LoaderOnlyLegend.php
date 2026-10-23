<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:21
 */

namespace ADD\PR;

use ClinicLE\Client\Container\AbstractLegend;
use ClinicLE\Client\Container\HtmlCreatorInterface;
use ClinicLE\Content\Render\HtmlElement;

class LoaderOnlyLegend extends AbstractLegend implements HtmlCreatorInterface
{
    function createHtml()
    {
        $html = new HtmlElement();
        $html->startElement("div", array("class" => "alert alert-success top-gap","ng-show"=>"control.data
            .loadingGrid"));

        $html->startElement("span");

        $html->addTextElement("i", "",array("class"=>"fa fa-spinner fa-spin fa-2x fa-fw"));
        $html->addTextElement("span","Loading...");

        $html->endElement("span");

        $html->endElement("div");

        $html->startElement("div");
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridMessage","ng-bind-html"=>"control.data
        .gridMessage","class"=>"alert alert-success"));
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridError","ng-bind-html"=>"control.data.gridError","class"=>"alert alert-error"));
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridWarning","ng-bind-html"=>"control.data.gridWarning","class"=>"alert alert-warning"));
        $html->endElement("div");
        return $html;
    }

}