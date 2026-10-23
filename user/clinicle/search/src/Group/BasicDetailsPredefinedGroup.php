<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:56
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\SearchType\SearchTypeFactory;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\SearchType\StringSearchType;
use ClinicLE\Search\SearchType\DateSearchType;
use ClinicLE\Search\SearchType\IdentifierSearchType;
use ClinicLE\Search\SearchType\ListSearchType;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\WhereClause;

class BasicDetailsPredefinedGroup extends AbstractPredefinedGroup
{
    function getName() {
        return "basic_demog";
    }

    function getPredefinedTitle() {
        return "Basic demographics";
    }

    function getTableExpression() {
        return "patient";
    }
    function getPredefinedAttributes() {
        return array(
            "identifier"=>"text",
            "first_name"=>"text",
            "last_name"=>"text",
            "date_of_birth"=>"text",
            "deceased"=>"checkbox"
        );
    }

}