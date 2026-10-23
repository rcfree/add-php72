<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:59
 */

namespace ClinicLE\Client\Container\Cell;
use ClinicLE\Client\Container\Table;

class ExpressionCell extends AbstractCell
{
    private $fieldExpression;

    /**
     * @param null $fieldExpression
     */
    public function setFieldExpression($fieldExpression)
    {
        $this->fieldExpression = $fieldExpression;
    }

    /**
     * @return null
     */
    public function getFieldExpression()
    {
        return $this->fieldExpression;
    }

    function __construct($heading, $fieldExpression=null) {
        $this->fieldExpression = $fieldExpression;
        parent::__construct($heading);
    }
    function getCell()
    {
        return $this->fieldExpression;
    }
}