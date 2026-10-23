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
use ClinicLE\Client\Container\Table;
use ClinicLE\Content\Render\HtmlElement;

class SearchLegend extends AbstractLegend implements HtmlCreatorInterface
{
    private $table;
    function __construct(Table $table) {
        $this->table = $table;
    }
    function createHtml()
    {
        $html = new HtmlElement();
        $html->addTextElement("div", "No " . $this->table->getTitle() . "s", array("ng-show" =>
                $this->table->getDataSetName().".length==1 && !control.data.gridError", "class" => "alert alert-success top-gap no-print"));
        return $html;
    }

}