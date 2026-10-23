<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/07/17
 * Time: 14:43
 */

namespace ClinicLE\Search\Group;


use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Search\Util\WhereClause;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\Search\SearchType\SearchTypeFactory;

abstract class AbstractPredefinedGroup extends AbstractGroup
{
    function getCategoryName() {
        return "common";
    }

    function getTitle() {
        return "[predefined] ".$this->getPredefinedTitle();
    }

    abstract function getPredefinedTitle();
    function populatePredefinedFilter(WhereClause $where, GroupQuery $groupQuery, ParamSelectStatement
    $select) {
        foreach($this->getPredefinedFilters() as $filter) {
            $where->addCondition($groupQuery->getTableAlias().".".$filter[0],$filter[1]);
        }
    }
    function getPredefinedFilters() {
        return array();
    }
    function populateSelect(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $where = new WhereClause("AND");
        $this->populatePredefinedFilter($where,$groupQuery, $select);
        if($where->hasConditions()) {
            $select->addWhereClause($where, $groupQuery->getExternalLogic());
        }
        parent::populateSelect($select,$groupQuery);
    }
    function getPredefinedAttributes() {
        return array();
    }
    function initQuery(GroupQuery $groupQuery) {
        $rff = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        foreach($this->getPredefinedAttributes() as $name => $type) {
            if(is_array($type)) {
                $field = $rff->newInstance(array("Field"=>$name,"Type"=>$type[0],"Options"=>$type[1]));
            }
            else {
                $field = $rff->newInstance(array("Field"=>$name,"Type"=>$type));
            }
            $this->addSearchType(SearchTypeFactory::createFromField($field));

            $groupQuery->addAttribute($name);
        }
    }


}