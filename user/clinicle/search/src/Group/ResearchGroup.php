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

class ResearchGroup extends AbstractGroup
{
    const CONSENT_TABLE="consent_record";
    function getCategoryName() {
        return "research_db";
    }
    function getName() {
        return "status";
    }
    function getTitle() {
        return "Research status";
    }
    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {
        //$select->addJoin("right join ".self::CONSENT_TABLE." ".$groupQuery->getTableAlias()." on ".$groupQuery->getTableAlias().".patient_id = ".AbstractPatientPageGroup::PATIENT_ALIAS.".id");
        $searchRepo = \CLE::svc(SearchServices::SEARCH_REPOSITORY);

	if(!$this->isPrepareEnabled()) {
            return;
        }

        $searchRepo->createConsentRecord();
    }
    function getTableExpression() {
        return self::CONSENT_TABLE;
    }
    function __construct() {
        $rff = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);


        $this->addSearchType(new ListSearchType($rff->newInstance(array("Field"=>"current_status","Options"=>self::getStatusOptions(),"Type"=>"dropdown")),"Current status"));
        $this->addSearchType(new DateSearchType($rff->newInstance(array("Field"=>"consent_form_date","Type"=>"date")),"Consent form date",true));
        $this->addSearchType(new IdentifierSearchType($rff->newInstance(array("Field"=>"ilh_identifier","Type"=>"text")),"ILH identifier"));
        $this->addSearchType(new ListSearchType($rff->newInstance(array("Field"=>"add_to_recruit_db","Type"=>"yes_no")),"Add to recruitment DB?"));
        $this->addSearchType(new ListSearchType($rff->newInstance(array("Field"=>"add_to_research_db","Type"=>"yes_no")),"Add to research DB?"));

    }
    function populateFrom(ParamSelectStatement $select, GroupQuery $groupQuery)
    {
        //$joins = $select->getJoins();
        //$lastJoin = array_pop($joins);
        //\CLE::info("lastJoin:",$lastJoin);
        \CLE::info("addSectionPatientJoin");
        $select->addGroupJoin($groupQuery,"patient_id","patient_id");
    }

    static function getStatusOptions() {
        return array("1"=>"Unsuitable for recruitment", "2"=>"Patient requests no contact", "3"=>"Available for recruitment","4"=>"Currently in study","5"=>"Not currently available",);
    }

    function getSectionType() {
        return self::CONSENT_TABLE;
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
