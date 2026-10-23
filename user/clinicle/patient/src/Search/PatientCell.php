<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/05/18
 * Time: 12:21
 */

namespace ClinicLE\Patient\Search;

use ClinicLE\Client\Container\Cell\BooleanCheckCell;
use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\MultiCell;
use CLE;
use ClinicLE\Content\Render\HtmlElement;

class PatientCell extends BooleanCheckCell
{
    function __construct()
    {
        parent::__construct("Patient Name", "row.added_to_db");
    }

    function getFalseExpression()
    {
        $addHtml = new HtmlElement();
        $addHtml->addTextElement("a", "<i class='icon-plus-sign icon-white'></i>&nbsp;Add",
            array("ng-click" => "gotoPage('patient/new?identifier=' + row.identifier)", "class" => "btn btn-primary btn-small")
        );
        $addHtml->addHtml("&nbsp;");
        $addHtml->addTextElement("a", "<i class='icon-plus-sign'></i>&nbsp;Quick add",
            array("ng-click" => "gotoPage('patient/quickadd?identifier=' + row.identifier)", "class" => "btn btn-small")
        );

        $expressionCell = new ExpressionCell("", "<span style='color:red'>*</span>{{ row.patient_name }}");
        return $expressionCell->getCell()."&nbsp;".$addHtml->output();
    }
    function getTrueExpression() {
        $multiCell = new MultiCell("",array(
            new GotoPageCell("","{{ row.patient_name }}","'/?pid=' + row.id + '&t=".CLE::db('token')->generate()."'"),
            new ExpressionCell("","<span ng-show='row.deleted' class='label label-important'>Deleted</span>")
        ));
        return $multiCell->getCell();
    }
}