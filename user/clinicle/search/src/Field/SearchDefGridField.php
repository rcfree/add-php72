<?php

namespace ClinicLE\Search\Field;
use ClinicLE\Client\Container\Legend;
use ClinicLE\Client\Container\SearchDefLegend;
use ClinicLE\Client\Container\Table;
use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Search\Cell\SearchFilterCell;
use ClinicLE\Search\Cell\SearchAttributesCell;
use ClinicLE\Search\Cell\SearchSettingsCell;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:26
 */
class SearchDefGridField extends \ADD_Field_Base
{
    function createMarkup($page) {
            $html = \CLE::web()->getContent();
            $container = new \ClinicLE\Client\Container\Container("search_groups","SearchGridController");
            $container->putProperty("dataurl","/search/groups");
            $container->putProperty("refresh","search_groups");
            $table = new Table("search group","control.data.grid","row");
            $table->setTableClass("table table-condensed table-bordered table-striped");
            $table->setPaged(true);
            $table->setLoader(true);
            $table->addCell(new ExpressionCell("Search group","{{ row.title }}"));
            $table->addCell(new SearchFilterCell());
            $table->addCell(new SearchAttributesCell());
        $table->addCell(new SearchSettingsCell());
        $table->addCell(new ExpressionCell("","<button class='btn btn-warning' ng-click='deleteSearchGroup(row.id)'><i class='icon-remove'></i></button>"));

            $container->addToContent($table);
            $html->addHtml($container->createHtml());
            return $html;


    }
}