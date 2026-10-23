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
use ClinicLE\Client\Container\Table;

class SearchFilterCell extends \ClinicLE\Client\Container\Cell\ExpressionCell
{
    function __construct() {
        $html = new HtmlElement();
        $table = new Table("","row.filters","filter");
        $table->setCountHidden(true);
        $html->addTextElement("button","Add filter..",array("class"=>"btn btn-mini btn-default","ng-click"=>"addFilter(row.id)"));
        $table->addCell(new ExpressionCell("Field","{{ filter.field_title }}"));
        $table->addCell(new ExpressionCell("Operation","{{ filter.operation_title }}"));
        $table->addCell(new ExpressionCell("Values","{{ filter.values_title }}"));
        $table->addCell(new ExpressionCell("P","{{ filter.param }}"));
        $table->addCell(new ExpressionCell("","<button class='btn btn-mini btn-success' ng-click='editFilter(row.id,\$index)'><i
 class='icon-pencil'></i></button><button class='btn btn-mini btn-warning' ng-click='deleteFilter(row.id,\$index)'><i
 class='icon-remove'></i></button>"));

        $html->addHtml($table->createHtml());
        $html->addHtml($this->createModal()->create());
        $this->setFieldExpression($html);
    }

    function createModal() {
        $modal = new \ADD_Content_Html_ModalHelper("Add filter","search_groups-modal");
        $modal->addButton("<i class='icon-remove'></i>&nbsp;Close",array("ng-click"=>"closeItem()"));

        $modal->addButton("<i class='icon-plus icon-white' ng-hide='saving'></i><img src='/module/clinicle/client/images/ajax-button-white.gif' ng-show='saving'/> &nbsp;Save item",array("ng-click"=>"saveItem()","class"=>"btn-primary save-button"));
        return $modal;
    }
    function getHeading() {
        return "Filters";
    }
}