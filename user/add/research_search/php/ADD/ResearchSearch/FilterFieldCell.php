<?php

namespace ADD\ResearchSearch;
use ClinicLE\Client\Container\Cell\ExpressionCell;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/11/16
 * Time: 15:53
 */
class FilterFieldCell extends ExpressionCell
{
    function __construct($heading, $fieldExpression, $filterField)
    {
        parent::__construct($heading, $fieldExpression);
    }

    function getCell()
    {
        $html = new \ADD_Content_Html();
        $html->addTextElement("a",$this->getFieldExpression(),array("href"=>"javascript:;","ng-click"=>"control
        .field.".$this->getFilterField()."==row.".$this->getFieldExpression()));
        return $html->output();
    }
}