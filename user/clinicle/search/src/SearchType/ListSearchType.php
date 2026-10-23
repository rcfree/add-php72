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

class ListSearchType extends AbstractSearchType
{
    function createRenderField($name,$title, $operation) {
        $rowFieldFactory = \CLE::sys()->getFactory(SystemFactories::ROW_FIELD);

        if(in_array($operation,array("nn","nu"))) {
            $field = $rowFieldFactory->newInstance(array("Type"=>"hidden","Field"=>$name,"Title"=>$title));
        }
        else if(in_array($operation,array("eq","ne"))) {
            $field = $rowFieldFactory->newInstance(array("Type"=>"dropdown","Options"=>$this->getField()->getOptions()->getOptionList(),"Field"=>$name,"Title"=>$title,"Settings"=>array("empty_option"=>"None selected")));
        }
        else {
            $field = $rowFieldFactory->newInstance(array("Type"=>"checkboxes","Options"=>$this->getField()->getOptions()->getOptionList(),"Field"=>$name,"Title"=>$title,"Settings"=>array("columns"=>6,"empty_option"=>"None selected")));
        }
        return $field;
    }

    function processAttribute($row, \ClinicLE\Search\Query\GroupQuery $groupQuery)
    {
        $incFieldValues = $groupQuery->getSettings()->get("include_field_values");
        $fieldAlias = $this->getFieldAlias($groupQuery);
        $cell = $row[$fieldAlias];
        if ($cell===null) {
            if($incFieldValues) {
                $row[$fieldAlias . "[value]"] = "";
            }
            $row[$fieldAlias] = $groupQuery->getSettings()->get("null_label", "NULL");
            return;
        }
        elseif(Util::isEmpty($cell)) {
            if($incFieldValues) {
                $row[$fieldAlias . "[value]"] = "";
            }
            $row[$fieldAlias]=$groupQuery->getSettings()->get("empty_label","None selected");
            return;
        }
        $options = $this->getField()->getOptions();
        $label = $options->findLabelWithValue($cell);

        $row[$fieldAlias] = !Util::isEmpty($label) ? $label : "'" . $cell . "' missing";
        if($incFieldValues) {
            $row[$fieldAlias . "[value]"] = $cell;
        }
    }
}