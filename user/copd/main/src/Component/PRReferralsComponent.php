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


class PRReferralsComponent extends AbstractComponent
{
    function getType() {
        return "PRReferrals";
    }
    function title(&$title) {
        $title = "PR referrals";
    }
    function menuPath(&$menuPath) {
        $menuPath = "copd/prrefs";
    }
    function displayAccess()
    {
        if(!$this->getPage()->getPermission("view")) {
            throw new AccessFailedException("You do not have permission to view the PR referrals");
        }
    }
    function expectedPermissions(&$expPerms)
    {
        $expPerms[]="view";
    }

    function generate() {
        $html = $this->getPage()->html();

        $container = new Container("pr-referrals","PRReferralsController");
        $container->putProperty("dataurl","/copd/pr-referrals");

        $table = new Table("PR referral", "control.data.grid", "row");
        $table->setPaged(true);
        $table->setLoader(true);

        $table->addCell(new ExpressionCell("S number","{{ row.identifier }}"));
        $table->addCell(new ExpressionCell("Patient Details","{{row.last_name}}, {{row.first_name}} ({{row.date_of_birth}})"));
        $table->addCell(new FieldCell("Date of referral", "referral_date.value"));
        $table->addCell(new FieldCell("Main diagnosis", "main_diagnosis.label"));

        $editPr = new EditExternalRecordAction(\CLE::sys()->getPageFromPim("pr:referral"),"section_id");
        $editPr->setButtonText("PR referral");

        $table->addCell(new RowActionCell($editPr));


        $container->addToContent($table);

        $html->addHtml($container->createHtml());
        return $html;
    }
}