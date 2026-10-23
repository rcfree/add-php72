<?php
namespace ClinicLE\Search\Field;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Client\Util\AngularJS;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Search\Cell\SearchResultCell;

class SearchSavedGridField extends \ADD_Field_EncounterGrid {

	function addMarkup($page) {
		$this->setupInfo($page);

        $container = new \ClinicLE\Client\Container\Container($this->getName(),"EncounterGridController");

        $grid = new \ClinicLE\Client\Container\Table("Saved search","control.data.grid","row");
        $grid->setLoader(true);

        //$grid->setPaged(true);
        //$grid->setNoHeadings(true);
		//$beforeActions = $this->_instantiateRowActions("before_column_actions");
		//$afterActions = $this->_instantiateRowActions("after_column_actions");

		
		$this->_addGridProperties($container,$grid);
		
		$this->addFilters($container);
        $grid->addCell(new GotoPageCell("","{{ row.title }}","'/search/definition?id=' + row.id"));
        $grid->addCell(new ExpressionCell("Owner","{{ row.owner }}"));
        $html = new HtmlElement();
        $html->startElement("button",array("class"=>"btn btn-warning","ng-click"=>AngularJS::createCustomCall("deleteSearch",array("row.id"))));
        $html->addHtml("<i class='icon-remove'></i>");
        $html->endElement("button");
        $grid->addCell(new ExpressionCell("",$html));

        $content = $container->getContent();
        $content->addHtml($grid->createHtml());
		$page->html()->addHtml($container->createHtml($page));
		
	}
	function getDataUrl() {
		return "/search/list";
	}

}
