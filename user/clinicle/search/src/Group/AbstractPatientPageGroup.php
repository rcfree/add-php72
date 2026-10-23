<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Module\ModuleCollection;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\SearchType\AgeSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Service\CoreServices;

class AbstractPatientPageGroup extends AbstractPageGroup
{
    const PATIENT_ALIAS="patient_1";
    function getCategoryName() {
        return "patient";
    }
    function getName() {
        return "patient";
    }

    function getTableExpression() {
        return "patient";
    }

    function populateFrom(ParamSelectStatement $select, GroupQuery $groupQuery) {
        //\CLE::info("class:".get_class($this));
        $select->addGroupJoin($groupQuery);
    }
    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {
        if(!$this->isPrepareEnabled()) {
            return;
        }
        $searchRepo = \CLE::svc(SearchServices::SEARCH_REPOSITORY);

        \CLE::info("update patients");
        $searchRepo->createPatients();
    }
    function getPatientIdField() {
        return "id";
    }
    function getSectionIdField() {
        return null;
    }
}