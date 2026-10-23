<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:59
 */

namespace ClinicLE\Client\Container\Cell;
use ClinicLE\Client\Container\Table;

class GotoPageFieldCell extends GotoPageCell
{
    private $emptyValue = "- empty -";
    function __construct($heading, $fieldName, $linkExpression) {
        $this->fieldName = $fieldName;
        $this->setLinkExpression($linkExpression);
        parent::__construct($heading,null, $linkExpression);
    }
    function init(Table $table) {
        $this->setFieldExpression("{{ ".$table->getDataItemName().".".$this->fieldName."!= null ? "
            .$table->getDataItemName
            ().".".$this->fieldName." : '".$this->getEmptyValue()."' }}");
    }

    /**
     * @return string
     */
    public function getEmptyValue()
    {
        return $this->emptyValue;
    }

    /**
     * @param string $emptyValue
     */
    public function setEmptyValue($emptyValue)
    {
        $this->emptyValue = $emptyValue;
    }

}