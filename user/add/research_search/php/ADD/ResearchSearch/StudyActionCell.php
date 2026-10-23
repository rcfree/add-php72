<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 25/11/2016
 * Time: 08:47
 */

namespace ADD\ResearchSearch;


use ClinicLE\Client\Container\Cell\AbstractCell;
use ClinicLE\Client\Container\Container;
use ClinicLE\Client\Container\Table;
use ClinicLE\Content\Render\HtmlElement;

class StudyActionCell extends AbstractCell
{
    function getCell()
    {
        $html = new \ADD_Content_Html();
        $html->startElement("span",array("ng-disabled"=>"control.data.loadingGrid"));
        $html->startElement("div",array("ng-show"=>"row.current_status!='Unsuitable for recruitment'"));

        $html->startElement("div", array("ng-show"=>"row.study_status==''"));
        $html->addTextElement("button","Sent PIS",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-sentpis',row.patient_id,row.study_id,'Sent PIS')"));
        $html->endElement("div");

        $html->startElement("div", array("ng-show"=>"row.study_status=='Awaiting enrolment'"));
        $html->addTextElement("button","Enrol",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-studyenrol',row.patient_id,row.study_id,'Enrol participant in study')"));
        $html->addTextElement("button","Not interested",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-notinter',row.patient_id,row.study_id,'Not interested in study')"));
        $html->addTextElement("button","Re-sent PIS",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-sentpis',row.patient_id,row.study_id,'Re-sent PIS')"));
        $html->addTextElement("button","Screen fail",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-screenfail',row.patient_id,row.study_id,'Screen fail')"));
        $html->endElement("div");
        $html->startElement("div", array("ng-show"=>"row.study_status=='Enrolled'"));
        $html->addTextElement("button","Complete",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-studycomp',row.patient_id,row.study_id,'Complete study')"));
        $html->addTextElement("button","Withdraw",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-withdraw',row.patient_id,row.study_id,'Withdraw from study')"));
        $html->addTextElement("button","Screen fail",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-screenfail',row.patient_id,row.study_id,'Screen fail')"));
        $html->addTextElement("button","Update no",array("type"=>"button","class"=>"btn btn-mini","ng-click"=>"addItem('research:action-updateno',row.patient_id,row.study_id,'Update study number')"));
        $html->endElement("div");
        $html->endElement("div");
        $html->endElement("span");
        return $html;
    }

    function afterTable(Table $table) {
        $modal = new \ADD_Content_Html_ModalHelper("","research-search-modal");
        $modal->addButton("<i class='icon-remove'></i>&nbsp;Close",array("ng-click"=>"closeItem()"));

        $modal->addButton("<i class='icon-plus icon-white' ng-hide='saving'></i><img src='/module/clinicle/client/images/ajax-button-white.gif' ng-show='saving'/> &nbsp;Save action",array("ng-click"=>"saveItem()","class"=>"btn-primary save-button"));
        return $modal->create("Loading content...",true);
    }
}