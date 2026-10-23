<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 17/10/17
 * Time: 10:26
 */

namespace ClinicLE\Client\Container;


class StandardLegend extends AbstractLegend
{
    function isLoader() {
        return true;
    }
    function __construct(LegendTable $table) {
        $this->table = $table;
    }
    function createHtml()
    {
        $html = new \ADD_Content_Html();
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridError","ng-bind-html"=>"control.data.gridError","class"=>"alert alert-error"));
        $html->addTextElement("div","",array("ng-show"=>"control.data.gridWarning","ng-bind-html"=>"control.data.gridWarning","class"=>"alert alert-warning"));

        if ($this->table->isNoCountAsError()) {
            $html->addTextElement("div", "Sorry no " . $this->table->getTitle() . "s were found matching your search.", array("ng-show" => "control.data.totalRows==0 && !control.data.gridError", "class" => "alert alert-error"));
        }

        if (!$this->table->isCountHidden()) {
            $html->startElement("div", array("class" => "alert alert-success top-gap"));
            if ($this->isLoader()) {
                $ajaxLoader = new \ADD_Content_Html();

                $ajaxLoader->startElement("span", array("ng-show" => "control.data.loadingGrid"));

                $ajaxLoader->addTextElement("i", "", array("class" => "fa fa-spinner fa-spin fa-2x fa-fw"));
                $ajaxLoader->addTextElement("span", "Loading...");

                $ajaxLoader->endElement("span");
                $html->addHtml($ajaxLoader);
            }
            if ($this->table->isPaged()) {

                $html->addTextElement("div", "No " . $this->table->getTitle() . "s", array("ng-show" => "control.data.gridRows==0 && !control.data.gridError", "class" => "alert alert-success top-gap"));
                $html->addTextElement("span", "Not loaded", array("style" => "margin-right:1em", "ng-show" => "!control.data.grid && !control.data.loadingGrid"));
                $html->addTextElement("span", "{{control.data.firstRow}} - {{control.data.lastRow}} of {{control.data
           .totalRows}} "
                    . $this->table->getTitle() . "{{ control.data.totalRows == 1 ? '' : 's' }}", array("style" => "margin-right:1em", "ng-show" => "control.data.gridRows>0 && !control.data.loadingGrid"));
                $html->addTextElement("button", "<i class='icon-backward'></i>&nbsp;Prev", array("class" => "btn btn-small", "type" => "button", "ng-click" => "control.previousPage()", "ng-show" => "control.data.loadingGrid==false && control.data.lessRows"));
                $html->addTextElement("button", "<i class='icon-forward'></i>&nbsp;Next", array("class" => "btn btn-small", "type" => "button", "ng-click" => "control.nextPage()", "ng-show" => "control.data.loadingGrid==false && control.data.moreRows"));
            } else {
                if ($this->getCountType() == self::COUNT_ROWS) {
                    $html->addTextElement("span", "{{control.data.gridRows }} " . $this->table->getTitle() . "{{ control.data.totalRows == 1
                 ? '' : 's' }}");
                } else {
                    $html->addTextElement("span", "{{ control.data.gridColumns-1 }} " . $this->table->getTitle() . "{{ control.data.gridColumns-1 == 1
                 ? '' : 's' }}");
                }
            }

            $html->endElement("div");

        }
        return $html;
    }
    
}