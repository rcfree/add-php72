<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:37
 */

namespace ClinicLE\Search\SearchType;


use ClinicLE\Common\Config\ArrayConfig;
use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\CheckboxesField;
use ClinicLE\Entity\Field\DropdownField;
use ClinicLE\Entity\OptionList\DefinedOptionList;
use ClinicLE\Search\Group\GroupQuery;
use ClinicLE\System\Factory\SystemFactories;

class AutocompleteSearchType extends AbstractSearchType
{

    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        if($operation == "eq" || $operation == "ne") {
            $settings = $this->getField()->getSettings();
            $settings["db_populated"]=0;
            $field = $rowFieldFactory->newInstance(array("Type"=>"autocomplete","Settings"=>$settings,"Field"=>$name,"Title"=>$title));
        }
        else {
            $field = $rowFieldFactory->newInstance(array("Type"=>"text","Field"=>$name,"Title"=>$title));
        }
        return $field;
    }

    function processAttribute($row, \ClinicLE\Search\Query\GroupQuery $groupQuery)
    {
        $incFieldValues = $groupQuery->getSettings()->get("include_field_values");
        $fieldAlias = $this->getFieldAlias($groupQuery);
        $cell = $row[$fieldAlias];
        if (Util::isEmpty($cell)) {
            if($incFieldValues) {
                $row[$fieldAlias . "[value]"] = "";
            }
            return "NULL";
        }
        $options = $this->getField()->newOptionInstance();
        $label = $options->findLabelWithValue($cell);
        $row[$fieldAlias] = $label ? : "'" . $cell . "' missing";
        if($incFieldValues) {
            $row[$fieldAlias . "[value]"] = $cell;
        }
    }
}