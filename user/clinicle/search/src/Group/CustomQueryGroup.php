<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\SearchType\CustomQuerySearchType;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\SearchType\StringSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\SearchType\IdentifierSearchType;
use ClinicLE\Search\SearchType\ListSearchType;
use ClinicLE\Search\Query\GroupQuery;

class CustomQueryGroup extends AbstractGroup
{
    function getCategoryName() {
        return "custom";
    }
    function getName() {
        return "custom";
    }
    function getTitle() {
        return "Custom query";
    }
    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {

    }
    function getTableExpression() {
        return "consent";
    }
    function __construct() {
        $rff = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $this->addSearchType(new CustomQuerySearchType($rff->newInstance(array("Field"=>"custom_where","Type"=>"memo")),"Custom where"));
    }
}