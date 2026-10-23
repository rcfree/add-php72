<?php
namespace ClinicLE\Patient\Field;

use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\FieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\RowActionCell;
use ClinicLE\Client\Container\StandardLegend;
use CLE;
use ClinicLE\Patient\Search\MultiCell;
use ClinicLE\Patient\Search\PatientCell;
use ClinicLE\Patient\Search\StatusCell;

class SearchPatientsField extends \ADD_Field_EncounterGrid {
    function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
        parent::_addGridProperties($container, $table);
        //$container->putProperty("custom_pattern",htmlentities($this->getSetting('custom_pattern','cell.value')));
    }
    function addMarkup($page) {
        $this->setSetting("use_page","patient:search-results");
        $this->setSetting("includefields","id;search_sel;dob;fname;lname");

        $this->setupInfo($page);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),"PatientSearchGridController");

        $grid = new \ClinicLE\Client\Container\LegendTable("patient","control.data.grid","row");
        $legend = new StandardLegend($grid);
        $grid->setTableClass("table-bordered");
        $grid->setLegend($legend);
        $grid->setPaged(true);
        $grid->setAttr("ng-show","control.data.gridRows>0");

        $this->_addGridProperties($container,$grid);
        if($this->getSetting("includefields")) {
            $container->putProperty("includefields",$this->getSetting("includefields"));
        }
        $this->addFilters($container);
        $grid->addCell(new PatientCell("Patient Name"));
        $grid->addCell(new FieldCell("Date of Birth","date_of_birth"));
        $grid->addCell(new FieldCell("NHS number","nhs_no"));
        $grid->addCell(new FieldCell("S number", "identifier"));
        $grid->addCell(new FieldCell("Address 1", "address1"));
        $grid->addCell(new StatusCell());

        $content = $container->getContent();
        $content->addHtml($grid->createHtml());
        $page->html()->addHtml($container->createHtml($page));
    }
    function getDataUrl() {
        return $this->getSetting("dataurl","/search/patients");
    }

}