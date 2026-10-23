<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 11:00
 */

namespace EMBER\Blness\Component;
use ADD_Component as AbstractComponent;
use ADD_Content_Html as HtmlElement;
use ClinicLE\Client\Container\Cell\FieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\GotoPageFieldCell;
use ClinicLE\Client\Container\Container;
use ClinicLE\Client\Container\Table;
use ClinicLE\Common\Util;
use ClinicLE\Security\Exception\AccessFailedException;
use EMBER\Blness\Data\EmberPatientExtractorParams;

class EmberPatientListComponent extends AbstractComponent
{
    function getType() {
        return "EmberPatientList";
    }
    function title(&$title) {
        $title = "EMBER Patient List";
    }
    function menuPath(&$menuPath) {
        $menuPath = "ember/list";
    }
    function displayAccess()
    {
        if(!\CLE::sys()->getPageFromPim("blness:ember-patients")->getPermission("view")) {
            throw new AccessFailedException("You do not have permission to view the EMBER patient list");
        }
    }

    function generate() {
        $html = $this->getPage()->html();

        $visits = array("Visit 1A","Visit 1B","Visit 2");
        $startTabs = new HtmlElement();
        $startTabs->startElement("div",array("class"=>"tabbable"));
        $startTabs->startElement("ul",array("class"=>"nav nav-tabs","id"=>"ember-tabs"));


        \CLE::web()->addJavascript("$(document).ready(function() {  EMBER.Blness.refreshVisits('visit_1a'); $('#ember-tabs a:first').tab('show'); });");
        foreach($visits as $visit) {
            $visitName = Util::labelToName($visit);
            $startTabs->startElement("li");
            $startTabs->addTextElement("a", $visit, array("href" => "#" . $visitName, "data-toggle" => "tab","onclick"=>"EMBER.Blness.refreshVisits('$visitName')"));
            $startTabs->endElement("li");
        }

        $startTabs->endElement("ul");
        $startTabs->startElement("div",array("class"=>"tab-content"));
        $html->addHtml($startTabs);

        foreach($visits as $visit) {

            $visitName = Util::labelToName($visit);
            $container = new Container("ember-patients-".$visitName,"EmberPatientsController");
            $container->putProperty("dataurl","/ember/patients");
            $container->putProperty("ember_visit",$visit);
            $field = new HtmlElement();
            $field->startElement("form",array("class"=>"form-inline"));
            $field->addElement("input",array("type"=>"text","name"=>"ember_search","ng-keypress"=>"refreshOnEnter(\$event)","ng-model"=>"control.fields.ember_search","placeholder"=>"Enter study number","ng-blur"=>"refresh()"));
            $field->addTextElement("button","<i class='icon-search'></i>&nbsp",array("class"=>"btn","ng-click"=>"refresh()"));
            $field->startElement("span",array("class"=>"pull-right"));
            $field->addTextElement("label","Sort by&nbsp;",array("class"=>"checkbox"));
            $field->startElement("select",array("name"=>"ember_sort","style"=>"width:10em","ng-model"=>"control.fields.ember_sort","ng-init"=>"control.fields.ember_sort='study_number'","ng-change"=>"refresh()"));
            $field->addTextElement("option","number",array("value"=>"study_number"));
            $field->addTextElement("option","enroll date/time",array("value"=>"enrolled_datetime"));
            $field->endElement("select");
            $field->startElement("select",array("name"=>"ember_order","ng-model"=>"control.fields.ember_order","ng-change"=>"refresh()",
                "ng-init"=>"control.fields.ember_order='asc'","style"=>"width:5em"));
            $field->addTextElement("option","A->Z",array("value"=>"asc"));
            $field->addTextElement("option","Z->A",array("value"=>"desc"));
            $field->endElement("select");
            $field->endElement("span");
            $field->endElement("form");
            $module = \CLE::sys()->getModule("ember/blness");
            $params = new EmberPatientExtractorParams($module,$visit);


            $tabstart = new HtmlElement();
            $tabstart->startElement("div", array("class" => "tab-pane", "id" => $visitName));
            $table = new Table("EMBER patient", "control.data.grid", "row");
            $table->setPaged(true);
            $table->setLoader(true);

            $number = new GotoPageFieldCell("Number", "Number.value", "'/?pid=' + row.patient_id.value");
            $number->setEmptyValue("- none -");
            $table->addCell($number);
            $table->addCell(new FieldCell("Enrolled", "Enrolled.value"));
            $table->addCell(new FieldCell("Diagnosis", "Diagnosis.value"));


            $pageMap = $params->extractPageMap();

            foreach ($pageMap as
                     $aname => $apage) {
                $alabel = $apage->getSetting("EmberPatients.label");

                $table->addCell(new GotoPageCell($alabel, "<i ng-class=\"row.$alabel.value == 1 ? 'fa fa fa-check-square-o fa-2x' : 'fa fa-square-o fa-2x'\"></i>", "'" . $apage->createMenuPath() . "?pid=' + row.patient_id.value + '&eid=' + row.parent_encounter_id.value"));
            }

            $tabend = new HtmlElement();
            $tabend->endElement("div");

            $container->addToContent($field);

            $container->addToContent($table);

            $html->addHtml($tabstart);
            $html->addHtml($container->createHtml());
            $html->addHtml($tabend);
        }

        $endTabs = new HtmlElement();
        $endTabs->endElement("div");
        $endTabs->endElement("div");

        $html->addHtml($endTabs);

        return $html;
    }
}