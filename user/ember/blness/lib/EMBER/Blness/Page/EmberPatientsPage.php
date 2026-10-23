<?php
namespace EMBER\Blness\Page;
use ClinicLE\Client\Data\EncounterExtractor;
use ClinicLE\Client\Data\EncounterExtractorParams;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\DataFrame\DataFrame;
use ClinicLE\DataFrame\MatrixDataFrame;
use ClinicLE\System\Service\CoreServices;
use CLE;
use EMBER\Blness\Data\EmberPatientExtractorParams;
use EMBER\Blness\Data\EmberPatientExtractor;
use EMBER\Blness\Data\EmberPatientsDataFrame;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 12:05
 */
class EmberPatientsPage extends \ADD_Page_Api
{
    function apiOutput()
    {
        $repo = CLE::svc(CoreServices::ENCOUNTER_REPOSITORY);
        /** @var  $query AbstractEncounterQuery */
        $query = $repo->createQuery();
        $queryRunner = CLE::svc(CoreServices::QUERY_RUNNER);
        $module = CLE::sys()->getModule("ember/blness");

        $params = new EmberPatientExtractorParams($module,CLE::sys()->getParam("ember_visit"));
        $params->setPageNo(CLE::sys()->getParam("page_no"));
        $params->setEmberSearch(CLE::sys()->getParam("ember_search"));
        $params->setSortField(CLE::sys()->getParam("ember_sort"));
        $params->setSortOrder(CLE::sys()->getParam("ember_order"));
        $params->setRowsPerPage(20);

        $edb = CLE::db('encounter');

        $encounterExtractor = new EmberPatientExtractor($query,$queryRunner, $edb);
        $dataFrame = new DataFrame($params);

        return $encounterExtractor->extractData($params,$dataFrame);
    }
    function createMenuPath()
    {
        return "ember/patients";
    }
    function getExpectedPermissions()
    {
        return array("view");
    }
}