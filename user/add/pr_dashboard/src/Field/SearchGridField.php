<?php
namespace ADD\PR\Field;

use ADD\PR\MultiGridHeader;
use ADD\PR\MultiGridLegend;
use ADD\PR\MultiGridTable;
use ADD\PR\SearchConfig;
use ADD\PR\SearchLegend;
use ADD\PR\SearchTable;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Client\Container\Table;
use ClinicLE\Search\Cell\SearchResultCell;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 09/10/17
 * Time: 20:12
 */
class SearchGridField extends \ADD_Field_Base {

    function addMarkup($page) {
        $parentField = $page->getComponent("Form")->getField($this->assertSetting("config_container"));
        $searchName = $this->getSetting("search_name");
        $searchSection = $parentField->getSearchConfig()->getSection($searchName);
        $grid = new SearchTable($searchSection->getSetting("title"), "control.data.grid.$searchName.data", "row");
        $legend = new SearchLegend($grid);
        $grid->setLegend($legend);
        $grid->setTableClass("table-bordered");

        $grid->addCell(new SearchResultCell($this->getSetting('headcol_width', '10em'), $this->getSetting('datacol_width',
            '5em')));
        $page->html()->addHtml($grid->createHtml());
    }

}