<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:02
 */

namespace ClinicLE\Search\SearchType;

use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\NumberField;
use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\System\Factory\SystemFactories;

class NumberSearchType extends AbstractSearchType
{

    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);
        if(in_array($operation,array("nn","nu"))) {
            $field = $rowFieldFactory->newInstance(array("Type"=>"hidden","Field"=>$name,"Title"=>$title));
        }
        else {
            $field = $rowFieldFactory->newInstance(array("Field" => $name, "Title" => $title, "Type" => "Text"));
        }
        return $field;
    }
    function getOperations() {
        $ops = parent::getOperations();
        $ops += array("in" => "in","ni" => "not in","gt"=>"greater than","ge"=>"greater or equal","lt"=>"less than","le"=>"less than or equal","bt"=>"between");
        return $ops;
    }

    function getFieldSelectExpression(GroupQuery $groupQuery)
    {
        $fieldName = $groupQuery->getTableAlias().".".$this->getFieldName();
        return "CONVERT(IF($fieldName='',NULL,$fieldName), DECIMAL)";
    }
    function getFieldWhereExpression(GroupQuery $groupQuery) {
        return $this->getFieldSelectExpression($groupQuery);
    }
}
