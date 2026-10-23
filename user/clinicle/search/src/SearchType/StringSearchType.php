<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;


use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\MemoField;
use ClinicLE\Entity\Field\TextField;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Search\Util\FilterLogics;
use ClinicLE\System\Factory\SystemFactories;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Data\Util\DatabaseUtil;
use ClinicLE\Search\Util\WhereClause;

class StringSearchType extends AbstractSearchType
{
    private $keyedValues = array();
    function getOperations()
    {
        $ops = parent::getOperations();
        $ops['in'] = "including (comma-separated)";
        return array_merge($ops, array(
            "li" => "is like",
            "nl" => "is not like",
            "in-lf" => "including (line-feed separated)",
            "in-lfid" => "including (line-feed separated with IDs)"
        ));
    }

    function createRenderField($name, $title, $operation)
    {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);
        $op = substr($operation, 0, 2);

        if ($op == "in" || $op == "ni") {
            $matches = array();
            $match = preg_match("/^including \((.+)\)$/", $operation, $matches);
            $field = $rowFieldFactory->newInstance(array("Field" => $name, "Title" => "Values", "Type" => "Memo", "Settings" => array("size" => 80, "height" => 6)));
        } else {
            $field = $rowFieldFactory->newInstance(array("Field" => $name, "Title" => $title, "Type" => "Text"));
        }

        return $field;

    }

    function getFilterClauseAndParams($filter, GroupQuery $groupQuery)
    {
        $opKey = $filter['operation'];

        $opType = substr($opKey,0,2);
        if($opType != "in") {
            return parent::getFilterClauseAndParams($filter, $groupQuery);
        }
        if ($opKey == "in-lf") {
            $values = $this->extractLineFeedValues($filter);
        }
        if ($opKey == "in-lfid") {
            $values = $this->extractLineFeedValues($filter,function($value) {
                $temp = explode("=",$value);
                return trim($temp[1]);
            });
        }
        $placeholders = DatabaseUtil::makePlaceholders($values);
        $clause = array($this->getFieldWhereExpression($groupQuery) . " in ($placeholders)", $values);

        return $clause;

    }

    function extractLineFeedValues($filter, $mapFunction=null) {
        if(!$mapFunction) {
            $mapFunction = function ($value) {
                return trim($value);
            };
        }
        $values = $this->getFilterValues($filter);

        $values = preg_split('/$\R?^/m', $values[0]);
        $values = array_map($mapFunction, $values);
        return $values;
    }

    function processAttribute($row, \ClinicLE\Search\Query\GroupQuery $groupQuery)
    {
        $filters = $groupQuery->getFilters();
        $fieldAlias = $this->getFieldAlias($groupQuery);
        foreach($filters as $filter) {
            if ($filter['field'] != $this->getFieldName()) {
                continue;
            }

            $opKey = $filter['operation'];
            if ($opKey != "in-lfid") {
                return;
            }
            $filterValues = $this->getFilterValues($filter);
            $values = preg_split('/$\R?^/m', $filterValues[0]);
            $keyedValues = array();
            foreach($values as $value) {
                $temp = explode("=", $value);
                $keyedValues[$temp[1]]=$temp[0];
            }
            $row[$fieldAlias . "[id]"] = Util::val($row[$fieldAlias],$keyedValues,"none");
        }
    }
    function addFilterToWhere(WhereClause $where, $filter, GroupQuery $groupQuery) {
        $keyedValues = $this->extractLineFeedValues($filter,function($value) {
            $temp = explode("=",$value);
            if (count($temp) == 1) {
		return $value;
		}
	return trim($temp[1]);
        });

        $values = array();
        foreach($keyedValues as $keyedValue) {
            $temp = explode("\t",$keyedValue);
            if(!isset($temp[1])) {
                continue;
            }
            $values[$temp[1]] = $temp[0];
        }
        $this->keyedValues = $values;
        parent::addFilterToWhere($where,$filter,$groupQuery);
    }
}
