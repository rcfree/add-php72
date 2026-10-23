<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:34
 */

namespace ClinicLE\Search\SearchType;

use ClinicLE\Common\Util;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Entity\Field\CheckboxesField;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Entity\OptionList\DefinedOptionList;
use ClinicLE\Search\Util\WhereClause;
use ClinicLE\System\Factory\SystemFactories;

class MultiListSearchType extends ListSearchType
{
    function getOperations()
    {
        return array(
            "eq"=>"equal",
            "ne"=>"not equal",
            "ao"=>"any of",
            "no"=>"none of",
        );
    }

    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        $field = $rowFieldFactory->newInstance(array("Type"=>"checkboxes","Options"=>$this->getField()->getOptions()->getOptionList(),"Field"=>$name,"Title"=>$title,"Settings"=>array("columns"=>6)));

        return $field;
    }

    function addFilterToWhere(WhereClause $where, $filter, GroupQuery $groupQuery) {

        $values = is_array($filter['value']) ? $filter['value'] : array($filter['value']);
        $clauseOp = " like ?";
        $op = $filter['operation'];
        if($op=="ne" || $op=="no") {
            $clauseOp = " not like ?";
        }
        if($op=="eq" || $op=="ne") {
            $clauseLogic = "AND";
        }
        else {
            $clauseLogic = "OR";
        }
        $fieldExp = $this->getFieldWhereExpression($groupQuery).$clauseOp;

        $subWhere = new WhereClause($clauseLogic);
        foreach ($values as $value) {
            $subWhere->addCondition($fieldExp,'%\"' . $value . '\":\"' . $value . '\"%');
        }
        $where->addSubWhereClause($subWhere);
    }

    function processAttribute($row,GroupQuery $groupQuery) {
        $fieldAlias = $this->getFieldAlias($groupQuery);
        if(!array_key_exists($fieldAlias,$row)) {
            $decode=null;
        }
        else {
            $cell = $row[$fieldAlias];
            $decode = Util::unserialize($cell);
            unset($row[$fieldAlias]);
        }
        if($decode==null) {
            foreach($this->getField()->getOptions()->getOptionList() as $key => $value) {
                $row[$fieldAlias."[$value]"]="";
            }
        }

        foreach($this->getField()->getOptions()->getOptionList()  as $key => $value) {
            $row[$fieldAlias."[$value]"]=isset($decode[$key]) ? "1" : "0";
        }
    }
}