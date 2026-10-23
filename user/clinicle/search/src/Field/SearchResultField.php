<?php
namespace ClinicLE\Search\Field;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Client\Container\StandardLegend;
use ClinicLE\Search\Cell\SearchResultCell;

class SearchResultField extends \ADD_Field_EncounterGrid {
    function _addGridProperties(\ClinicLE\Client\Container\Container $container, \ClinicLE\Client\Container\Table
    $table) {
	    parent::_addGridProperties($container, $table);
        //$container->putProperty("custom_pattern",htmlentities($this->getSetting('custom_pattern','cell.value')));
    }
	function addMarkup($page) {
		$this->setupInfo($page);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),"ResultGridController");

        $grid = new \ClinicLE\Client\Container\LegendTable("Search result","control.data.grid","row");
        $legend = new StandardLegend($grid);
        $grid->setTableClass("table-bordered");
        $grid->setLegend($legend);
        $grid->setPaged(true);
        $grid->setAttr("ng-show","control.data.gridRows>0");
		
		$this->_addGridProperties($container,$grid);
		$container->putProperty("searchurl",$this->getSetting("searchurl","/search/run"));
        if($this->getSetting("includefields")) {
            $container->putProperty("includefields",$this->getSetting("includefields"));
        }
		$this->addFilters($container);
        $grid->addCell(new SearchResultCell($this->getSetting('headcol_width','10em'),$this->getSetting('datacol_width',
            '5em')));
        $content = $container->getContent();
        $content->addHtml($grid->createHtml());
		$page->html()->addHtml($container->createHtml($page));
		
	}
	function getDataUrl() {
		return $this->getSetting("dataurl","/search/result");
	}

}
