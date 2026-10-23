<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:19
 */

namespace ClinicLE\Search\SearchType;



use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\System\Factory\SystemFactories;

class AgeSearchType extends AbstractSearchType
{
    private $toDateExpression = NULL;

    function __construct($field, $fieldTitle=null, $toDateExpression="CURDATE()") {
        $this->toDateExpression = $toDateExpression;
        parent::__construct($field,$fieldTitle);
    }
    function getName() {
        return $this->getFieldName()."_age";
    }

    function getFieldTitle()
    {
        return "Age (based on ".parent::getFieldTitle().")";
    }

    function getFieldSelectExpression(GroupQuery $groupQuery) {
        return "TIMESTAMPDIFF(YEAR, ".parent::getFieldSelectExpression($groupQuery).", ".$this->toDateExpression
            .")";
    }

    function getFieldWhereExpression(GroupQuery $groupQuery) {
        return "TIMESTAMPDIFF(YEAR, ".parent::getFieldWhereExpression($groupQuery).", "
            .$this->toDateExpression.")";
    }
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

}