<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:47
 */
namespace ClinicLE\Search\HomeTool;

use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Options\HomeTool\AbstractHomeTool;
use CLE;

class ExtendedSearchHomeTool extends AbstractHomeTool
{
    function createTool()
    {
        $htmlElement = new HtmlElement();
        $htmlElement->addTextElement("a","<i class='icon-plus'></i>&nbsp;Create new search",array("href"=>"/search/definition?action=create","class"=>"btn"));
        $htmlElement->addTextElement("a","<i class='icon-pencil'></i>&nbsp;Edit current search",array("href"=>"/search/definition?action=edit","class"=>"btn"));
        $htmlElement->addTextElement("a","<i class='icon-list'></i>&nbsp;View saved searches",array("href"=>"/search/saved","class"=>"btn"));
        return $htmlElement;
    }

    function toolTitle()
    {
        return "Extended Search";
    }

    public function isAvailable() {
        if(!CLE::sys()->getAccessManager()->isLoggedIn()) return false;
        $defPage = \CLE::sys()->getPageFromPim("search:definition");
        return $defPage->getPermission("view");
    }
}