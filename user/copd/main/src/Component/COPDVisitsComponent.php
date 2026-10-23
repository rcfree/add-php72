<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 22/08/17
 * Time: 11:00
 */

namespace COPD\Main\Component;
use ADD_Component as AbstractComponent;
use ADD_Content_Html as HtmlElement;
use ClinicLE\Client\Container\Cell\ExpressionCell;
use ClinicLE\Client\Container\Cell\FieldCell;
use ClinicLE\Client\Container\Cell\GotoPageCell;
use ClinicLE\Client\Container\Cell\GotoPageFieldCell;
use ClinicLE\Client\Container\Cell\RowActionCell;
use ClinicLE\Client\Container\Container;
use ClinicLE\Client\Container\Table;
use ClinicLE\Client\Data\EncounterExtractorParams;
use ClinicLE\Common\Util;
use ClinicLE\Security\Exception\AccessFailedException;
use COPD\Main\Action\EditExternalRecordAction;
use EMBER\Blness\Data\EmberPatientExtractorParams;
use PhpOffice\PhpWord\Style\Row;


class COPDVisitsComponent extends AbstractComponent
{
    function getType() {
        return "COPDVisits";
    }
    function title(&$title) {
        $title = "COPD visits";
    }
    function menuPath(&$menuPath) {
        $menuPath = "copd/visits";
    }
    function displayAccess()
    {
        if(!$this->getPage()->getPermission("view")) {
            throw new AccessFailedException("You do not have permission to view the COPD visits");
        }
    }
    function expectedPermissions(&$expPerms)
    {
        $expPerms[]="view";
    }

    function generate() {
        $html = $this->getPage()->html();

        $container = new Container("copd-referrals","COPDReferralsController");
        $container->putProperty("dataurl","/copd/visit-data");

        $table = new Table("COPD visit", "control.data.grid", "row");
        $table->setPaged(true);
        $table->setLoader(true);

        $table->addCell(new ExpressionCell("S number","{{ row.identifier }}"));
        $table->addCell(new ExpressionCell("Patient Details","{{row.last_name}}, {{row.first_name}} ({{row.date_of_birth}})"));
        $editCopd = new EditExternalRecordAction(\CLE::sys()->getPageFromPim("copd:demogadm"));
        $editCopd->setIcon("pencil");
        $editCopd->setBtnClass("btn btn-small btn-info");
        $table->addCell(new FieldCell("Date of admission", "date_of_admission.value"));
        $table->addCell(new FieldCell("Visit type", "visit_type.label"));
        $table->addCell(new FieldCell("Reviewed by team?", "reviewed_by_copd_team.label"));
        $editCopd->setButtonText("Edit visit..");


        $table->addCell(new FieldCell("Admitted from", "admitted_from.label"));
        $table->addCell(new FieldCell("Hospital", "admitted_to_hospital.label"));
        $table->addCell(new FieldCell("Ward", "admitted_to_ward.label"));
        $editPr = new EditExternalRecordAction(\CLE::sys()->getPageFromPim("pr:referral"),"pr_prog_id.value");
        $editPr->setButtonText("PR referral");

        #$table->addCell(new RowActionCell($editPr));
        $table->addCell(new RowActionCell($editCopd));

        $field = new HtmlElement();
        $field->startElement("form",array("class"=>"form-inline"));
        $field->addTextElement("label","Reviewed?");

        $field->startElement("select",array("name"=>"reviewed","style"=>"width:10em","ng-model"=>"control.fields.reviewed","ng-init"=>"control.fields.reviewed='1'","ng-change"=>"refresh()"));
        $field->addTextElement("option","any",array("value"=>"1"));
        $field->addTextElement("option","yes",array("value"=>"2"));
        $field->addTextElement("option","no",array("value"=>"3"));
        $field->addTextElement("option","missed",array("value"=>"4"));
        $field->endElement("select");
        $field->endElement("form");
        $container->addToContent($field);
        $container->addToContent($table);

        $html->addHtml($container->createHtml());
        return $html;
    }
}