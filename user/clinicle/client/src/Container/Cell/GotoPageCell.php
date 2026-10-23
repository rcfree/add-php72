<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 11:27
 */

namespace ClinicLE\Client\Container\Cell;

class GotoPageCell extends ExpressionCell
{
    private $linkExpression;

    /**
     * @param mixed $linkExpression
     */
    public function setLinkExpression($linkExpression)
    {
        $this->linkExpression = $linkExpression;
    }
    function __construct($heading, $fieldExpression, $linkExpression) {
        parent::__construct($heading,$fieldExpression);
        $this->linkExpression = $linkExpression;
    }
    function getCell()
    {
        $html = new \ADD_Content_Html();
        $html->addTextElement("a",$this->getFieldExpression(),array("href"=>"javascript:;","ng-click"=>"gotoPage(".$this->linkExpression.")"));
        return $html->output();
    }
}