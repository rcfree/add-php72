<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\SearchType\NumberSearchType;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\SearchType\StringSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\SearchType\IdentifierSearchType;
use ClinicLE\Search\SearchType\ListSearchType;
use ClinicLE\Search\Query\GroupQuery;

class AuditGroup extends AbstractGroup
{
    function getTitle() {
        return "Audit";
    }
    function getCategoryName() {
        return "audit";
    }
    function getName() {
        return "audit";
    }

    function getTableExpression() {
        return "audit";
    }

    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $searchRepo = \CLE::svc(SearchServices::SEARCH_REPOSITORY);
        if(!$searchRepo->shouldUpdateSearchTable("audit",\CLE::svc(CoreServices::LOCALE)->epoch())) {
            return;
        }
        $searchRepo->createAudit();
    }

    function __construct() {
        $rff = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);


        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"record_id","Type"=>"text")),"Record ID"));

        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"record_name","Type"=>"text")),"Record name"));
        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"record_type","Type"=>"text")),"Record type"));
        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"action","Type"=>"text")),"Action"));
        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"action_info","Type"=>"memo")),"Action info"));
        $this->addSearchType(new NumberSearchType($rff->newInstance(array("Field"=>"revision_no","Type"=>"number")),"Revision no"));
        $this->addSearchType(new StringSearchType($rff->newInstance(array("Field"=>"user_id","Type"=>"text")),"Username"));
        $this->addSearchType(new DateSearchType($rff->newInstance(array("Field"=>"timestamp","Type"=>"date_time")),
            "Timestamp",true));

    }

}