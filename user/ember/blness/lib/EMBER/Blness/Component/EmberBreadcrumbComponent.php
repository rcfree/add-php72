<?php
namespace EMBER\Blness\Component;
use ADD_Component as AbstractComponent;
use ClinicLE\Common\Util;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use ClinicLE\System\Service\CoreServices;
use EMBER\Blness\Data\EmberPatientExtractor;
use EMBER\Blness\Data\EmberPatientExtractorParams;
use CLE;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 13/07/17
 * Time: 10:00
 */
class EmberBreadcrumbComponent extends AbstractComponent
{
    function setup() {
        $this->getPage()->getEvents()->addListener(\ClinicLE\System\Event\ContentEvents::OPTION_BUTTONS,array
        ($this,"onOptionButtons"));
    }
    function getType() {
        return "EmberHeading";
}
    function breadcrumb(&$breadcrumb)
    {
        CLE::info("breadcrumb",$breadcrumb);
        $sys = \CLE::sys();
        $repo = CLE::svc(CoreServices::ENCOUNTER_REPOSITORY);
        /** @var  $query AbstractEncounterQuery */
        $query = $repo->createQuery();

        /** @var  $queryRunner QueryRunner */
        $queryRunner = CLE::svc(CoreServices::QUERY_RUNNER);
        $module = $sys->getModule("ember/blness");
        $parentPage = $this->getPage()->getParentPage();
        $emberSection = $parentPage->getSetting("EmberPatients.section_label");
        $params = new EmberPatientExtractorParams($module,$emberSection);
        $params->setPageNo(CLE::sys()->getParam("page_no"));

        $params->setRowsPerPage(1);
        $edb = CLE::db('encounter');

        $encounterExtractor = new EmberPatientExtractor($query,$queryRunner, $edb);

        $encounterExtractor->populateQuery($params);
        $query->filterByPatientId($sys->getParam("pid"));
        $query->filterByEncounterField("parent_encounter_id",$sys->getParam("eid"));
        $data = $queryRunner->getRows($query);
        if(count($data)==0) {
            return;
        }
        $number = $data[0]['Number'];
        $breadcrumbNo="EMBER: ". (Util::isEmpty($number) ? "none" : $number);

        array_splice($breadcrumb, 1,0,$breadcrumbNo);
    }
    function onOptionButtons(\ClinicLE\Component\Event\PageContentEvent $event) {

        $html = $event->getContent();
        $html->addTextElement("a","<i class='icon-list icon-white'></i>&nbsp;Goto EMBER patient list",array("class"=>"btn btn-info float-right",
            "href"=>"/ember/list"));
    }
}