<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/05/18
 * Time: 12:21
 */

namespace ClinicLE\Patient\Search;


use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Content\Render\HtmlElement;

class StatusCell extends ExpressionCell
{
    function __construct()
    {

        $html = new HtmlElement();
        $html->addTextElement("a", "<i class='icon-remove icon-white'></i>&nbsp;Delete",
            array("ng-show"=>"row.deletable","href"=>"javascript:;","ng-click"=>"deletePatient(row.id);","class"=>"btn btn-small btn-warning")
        );
        $html->addTextElement("span", "&nbsp;",array("ng-show"=>"row.purgable"));
        $html->addTextElement("a", "<i class='icon-fire icon-white'></i>&nbsp;Purge",
            array("ng-show"=>"row.purgable","href"=>"javascript:;","ng-click"=>"purgePatient(row.id);","class"=>"btn btn-small btn-danger")
        );
        parent::__construct("Status",$html->output());
    }

}