<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 11:51
 */

namespace EMBER\Blness\HomeTool;


use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\FieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\GotoPageFieldCell;
use ClinicLE\Client\Container\Cell\HtmlMatrixCell;
use ClinicLE\Client\Container\Cell\MatrixCell;
use ClinicLE\Client\Container\Container;
use ClinicLE\Client\Container\Table;
use ClinicLE\Common\Util;
use ClinicLE\Options\HomeTool\AbstractHomeTool;
use ADD_Content_Html as HtmlElement;
use ClinicLE\Options\HomeTool\AbstractPrivateHomeTool;
use ClinicLE\System\Factory\SystemFactories;
use EMBER\Blness\Data\EmberPatientExtractorParams;
use EMBER\Blness\Data\EmberPatientPages;

class EmberPatientsHomeTool extends AbstractPrivateHomeTool
{
    private $page;
    function setPage($page) {
        $this->page = $page;
    }
    function createTool()
    {
        $html = new HtmlElement();
        $html->addTextElement("a","<i class='icon-list'></i>&nbsp;Goto EMBER patient list",array("class"=>"btn",
            "href"=>"/ember/list"));
        return $html;
    }

    function toolTitle()
    {
        return "EMBER PATIENTS";
    }

    function isAvailableToUser()
    {
        return \CLE::sys()->getPageFromPim("blness:ember-patients")->getPermission("view");
    }
    function toolWeight()
    {
        return -99;
    }


}
