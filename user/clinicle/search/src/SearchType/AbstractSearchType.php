<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:59
 */

namespace ClinicLE\Search\SearchType;

use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Data\Util\DatabaseUtil;
use ClinicLE\Search\Group\AbstractGroup;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\PageHtmlContainer;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Search\Util\WhereClause;
use ClinicLE\System\Service\CoreServices;

abstract class AbstractSearchType
{
    private $group;
    private $fieldTitle;
    private $field;
    private $name;

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getFieldTitle()
    {
        return $this->fieldTitle;
    }

    /**
     * @param mixed $fieldTitle
     */
    public function setFieldTitle($fieldTitle)
    {
        $this->fieldTitle = $fieldTitle;
    }


    /**
     * @return AbstractGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }


    function __construct($field, $fieldTitle=null) {
        if(!is_object($field)) {
            throw new \InvalidArgumentException("Field '$field' argument must be object");
        }
        $htmlContainer = new PageHtmlContainer(new HtmlElement());
        $field->finaliseField($htmlContainer);
        $this->field = $field;
        if(!$fieldTitle) {
            $fieldTitle = $field->getName();
        }
        $this->fieldTitle = $fieldTitle;
    }
    function renderOperationHtml(HtmlElement $htmlElement, $operation)
    {
        $htmlContainer = new PageHtmlContainer($htmlElement);
        if($operation=="bt") {
            $field1 = $this->createRenderField("from_value","From", $operation);
            $field1->finaliseField($htmlContainer);
            $field1->createMarkup($htmlContainer);
            $field2 = $this->createRenderField("to_value","to", $operation);
            $field2->finaliseField($htmlContainer);
            $field2->createMarkup($htmlContainer);
            return;
        }
        if($operation=="nu" || $operation == "nn") {
            return;
        }
        $field = $this->createRenderField("value","Value",$operation);
        $field->finaliseField($htmlContainer);
        $field->createMarkup($htmlContainer);
        return $htmlElement;
    }

    abstract function createRenderField($name,$title,$operation);

    function getFieldName() {
        return $this->getField()->getName();
    }

    function getOperations() {
        return array(
            "eq"=>"equal",
            "ne"=>"not equal",
            "in" => "including",
            "ni" => "not including",
            "nu" => "is null",
            "nn" => "is not null",
            "gt"=>"greater than",
            "ge"=>"greater or equal",
            "lt"=>"less than",
            "le"=>"less than or equal",
            "bt"=>"between"
        );
    }

    function addFilterToWhere(WhereClause $where, $filter, GroupQuery $groupQuery) {
        list($clause, $params) = $this->getFilterClauseAndParams($filter, $groupQuery);
        $where->addCondition($clause, $params);
    }
    function getFilterValues($filter) {
        return Util::val("value",$filter,array());
    }
    function getFilterClauseAndParams($filter, GroupQuery $groupQuery) {
        $opKey = $filter['operation'];
        $op = "=";
        if($opKey=="nu" ) {
            return array("(".$this->getFieldWhereExpression($groupQuery)." is null or "
                .$this->getFieldWhereExpression($groupQuery)." = '')",array());
        }
        if($opKey=="nn" ) {
            return array("(".$this->getFieldWhereExpression($groupQuery)." is not null and "
                .$this->getFieldWhereExpression($groupQuery)." != '')",array());
        }
        $values = $this->getFilterValues($filter);

        if($opKey=="ge") {
            $op = ">=";
        }
        if($opKey=="gt") {
            $op = ">";
        }
        if($opKey=="lt") {
            $op = "<";
        }
        if($opKey=="le") {
            $op = "<=";
        }
        if($opKey=="ne") {
            $op = "!=";
        }
        if($opKey=="li") {
            $values[0]=$values[0];
            $op=" like ";
        }
        if($opKey=="nl") {
            $values[0]=$values[0];
            $op=" not like ";
        }
        if($opKey=="in") {
            $placeholders = DatabaseUtil::makePlaceholders($values);
            return array($this->getFieldWhereExpression($groupQuery)." in ($placeholders)",$values);
        }
        if($opKey=="ni") {
            $placeholders = DatabaseUtil::makePlaceholders($values);
            return array($this->getFieldWhereExpression($groupQuery)." not in ($placeholders)",$values);
        }
        if($opKey=="bt" ) {
            if(count($values)!=2) {
                throw new \InvalidArgumentException("Must have from and to value in between operation");
            }

            return array($this->getFieldWhereExpression($groupQuery)." between ? and ?",array($values[0],$values[1]));
        }

        return array($this->getFieldWhereExpression($groupQuery)." ".$op." ?",$values);
    }

    function addAttributeToSelect(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $select->addToSelect($this->getFieldSelectExpression($groupQuery)." as `".$this->getFieldAlias($groupQuery)."`");
    }
    function processAttribute($row, GroupQuery $groupQuery) {

    }

    function getFieldAlias(GroupQuery $groupQuery) {
        return $groupQuery->getGroup()->getName().".".$this->getName();
    }
    function getFieldSelectExpression(GroupQuery $groupQuery) {
        $groupByPatient = $groupQuery->getSettings()->get("group_by_patient");

        if($groupByPatient) {
            $intAlias = $groupQuery->getTableAlias()."_".uniqid("");
            return "(select group_concat(".$intAlias.".".$this->getFieldName().") from ".$groupQuery->getGroup()->getTableExpression()." ".$intAlias.
                " left join ".$groupQuery->getGroup()->getTableExpression()." on ".$intAlias.".id = ".$groupQuery->getTableAlias().".id group by ".$groupQuery->getTableAlias().".patient_id)";
        }
        return $groupQuery->getTableAlias().".".$this->getFieldName();
    }
    function getFieldWhereExpression(GroupQuery $groupQuery) {
        return $groupQuery->getTableAlias().".".$this->getFieldName();
    }
    function getName() {
        return $this->getField()->getName();
    }
    function getOperationsAsPairs() {
        $ops = $this->getOperations();
        if(!$ops) {
            return array();
        }
        $pairs = array();
        foreach($ops as $value => $label) {
            $pairs[]=array("value"=>$value,"label"=>$label);
        }
        return $pairs;
    }
    function getOperationTitle($operation) {
        return Util::val($operation,$this->getOperations());
    }

    function createObsFieldSubQuery($joinAlias, $sourceDbName) {
        return "(select if(o.value='', NULL, o.value) from {$sourceDbName}.observation o left join {$sourceDbName}.observation_field of on o.observation_field_id=of.id where o.encounter_id=`$joinAlias`.id and of.name='".$this->getFieldName()."')";
    }
}