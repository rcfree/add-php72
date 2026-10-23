<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/11/16
 * Time: 13:44
 */

namespace ClinicLE\Client\Container\Cell;


class FormFieldCell extends AbstractCell
{
    private $childFields;
    private $parentField;

    function __construct($childFields, $parentField) {
        $this->childFields = $childFields;
        $this->parentField = $parentField;
    }
    function getCell() {
        if (count($this->childFields) > 0) {
            $subResults = array();
            $joinChar = $this->parentField->getSetting('join_char') ? :
                " ";

            foreach ($this->childFields as $childField) {
                $subResults[] = "{{row." . $childField->getName() . ".label}}";
            }
            $cell = implode($joinChar, $subResults);
            return $cell;
        }
        $fieldText = "row." . $this->parentField->getName() . ".label";
        $cell = '<span ng-bind-html="' . $fieldText .  ' | trustHtml"></span>';
        return $cell;
    }
    function getHeading() {
        $colTitle = $this->parentField->getSetting('col_title');
        return $colTitle ? : $this->parentField->getTitle();
    }
}