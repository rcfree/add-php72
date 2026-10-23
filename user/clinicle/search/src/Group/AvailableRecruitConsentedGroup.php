<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\SearchType\StringSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\SearchType\IdentifierSearchType;
use ClinicLE\Search\SearchType\ListSearchType;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\WhereClause;

class AvailableRecruitConsentedGroup extends AbstractPredefinedGroup
{
    function getName() {
        return "consent_recruit";
    }

    function getPredefinedTitle() {
        return "Available and consented to recruitment";
    }

    function getTableExpression() {
        return ResearchGroup::CONSENT_TABLE;
    }

    function populateFrom(ParamSelectStatement $select, GroupQuery $groupQuery)
    {
        $select->addGroupJoin($groupQuery);
    }

    function getPredefinedFilters()
    {
        return array(
            array("add_to_recruit_db=?","Y"),
            array("current_status=?",3)
        );
    }
    function getPredefinedAttributes() {

        return array(
            "ilh_identifier"=>"text",
            "current_status"=>array("dropdown",ResearchGroup::getStatusOptions()),
            "add_to_recruit_db"=>"yes_no",
            "add_to_research_db"=>"yes_no",
        );

    }
    function getSectionType() {
        return ResearchGroup::CONSENT_TABLE;
    }

    function getSectionName() {
        return "consent";
    }

    function getPatientIdField() {
        return "patient_id";
    }
    function getSectionIdField() {
        return null;
    }
}