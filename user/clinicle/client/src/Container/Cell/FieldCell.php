<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 10/11/2016
 * Time: 10:59
 */

namespace ClinicLE\Client\Container\Cell;
use ClinicLE\Client\Container\Table;

class FieldCell extends ExpressionCell
{
    function __construct($heading, $fieldName) {
        $this->fieldName = $fieldName;
        parent::__construct($heading);
    }
    function init(Table $table) {
        $this->setFieldExpression("{{ ".$table->getDataItemName().".".$this->fieldName." }}");
    }

}