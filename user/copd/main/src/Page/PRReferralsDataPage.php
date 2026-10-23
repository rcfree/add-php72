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

class PRReferralsDataPage extends \ADD_Page_Api
{
    function apiOutput()
    {
        $repo = CLE::svc(CoreServices::ENCOUNTER_REPOSITORY);
        /** @var  $query AbstractEncounterQuery */
        $query = $repo->createQuery();

        $query->addPatientFields(array("identifier","first_name","last_name","date_of_birth","deceased"));
        $query->addObservationFields(array("referral_date","main_diagnosis"));
        $query->addEncounterField("parent_encounter_id","section_id");
        $query->addEncounterField("patient_id");
        $query->filterByObservationField("enrol_in_treatment",0,"=");
        $query->filterByObservationField("referred_by",10,"=");
        $queryRunner = CLE::svc(CoreServices::QUERY_RUNNER);

        $params = new EncounterExtractorParams("pr:referral");
        $params->setPageNo(CLE::sys()->getParam("page_no"));

        $params->setSortField("referral_date.value");
        $params->setSortOrder("DESC");
        $params->setRowsPerPage(20);

        $edb = CLE::db('encounter');
        $encounterExtractor = new EncounterExtractor($query,$queryRunner, $edb);

        $dataFrame = new DataFrame($params);

        $data = $encounterExtractor->extractData($params,$dataFrame);
        
        return $data;
    }
    function createMenuPath()
    {
        return "copd/pr-referrals";
    }
    function getExpectedPermissions()
    {
        return array("view");
    }
}