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

class LivingPatientsGroup extends AbstractPredefinedGroup
{
    function getName() {
        return "living_patients";
    }

    function getPredefinedTitle() {
        return "Living patients";
    }

    function getTableExpression() {
        return "patient";
    }

    function populatePredefinedFilter(WhereClause $where, GroupQuery $groupQuery, ParamSelectStatement $select)
    {
        $where->addCondition($this->getTableExpression().".deceased != ?",1);
    }
}