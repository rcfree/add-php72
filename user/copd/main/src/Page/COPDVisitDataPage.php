<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 10/07/18
 * Time: 22:01
 */

namespace COPD\Main\Page;

use ClinicLE\Client\Data\EncounterExtractor;
use ClinicLE\Client\Data\EncounterExtractorParams;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\DataFrame\DataFrame;

use ClinicLE\System\Service\CoreServices;
use CLE;

class COPDVisitDataPage extends \ADD_Page_Api
{
    function apiOutput()
    {
        $repo = CLE::svc(CoreServices::ENCOUNTER_REPOSITORY);
        /** @var  $query AbstractEncounterQuery */
        $query = $repo->createQuery();

        $query->addPatientFields(array("identifier","first_name","last_name","date_of_birth","deceased"));
        $query->addObservationFields(array("date_of_admission","visit_type","admitted_from","hospital_admitted_to","ward_admitted_to","reviewed_by_copd_team","pr_prog_id"));
        $query->addEncounterField("parent_encounter_id","section_id");
        $query->addEncounterField("patient_id");

        $reviewed = CLE::sys()->getParam("reviewed");

        switch($reviewed) {
            case 2:
                $query->filterByObservationField("reviewed_by_copd_team","Y");
                break;
            case 3:
                $query->filterByObservationField("reviewed_by_copd_team","N");
                break;
            case 4:
                $query->filterByObservationField("reviewed_by_copd_team","M");
                break;
        }

        $queryRunner = CLE::svc(CoreServices::QUERY_RUNNER);

        $params = new EncounterExtractorParams("copd:demogadm");
        $params->setPageNo(CLE::sys()->getParam("page_no"));

        $params->setSortField("date_of_admission");
        $params->setSortOrder("DESC");
        $params->setSortedAsDate(true);
        $params->setRowsPerPage(20);

        $edb = CLE::db('encounter');
        $encounterExtractor = new EncounterExtractor($query,$queryRunner, $edb);

        $dataFrame = new DataFrame($params);

        $data = $encounterExtractor->extractData($params,$dataFrame);
        
        return $data;
    }
    function createMenuPath()
    {
        return "copd/visit-data";
    }
    function getExpectedPermissions()
    {
        return array("view");
    }
}