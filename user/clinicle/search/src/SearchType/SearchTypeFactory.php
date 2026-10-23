<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:34
 */

namespace ClinicLE\Search\SearchType;


class SearchTypeFactory
{
    static function createFromField($field) {
        $fieldTitle = $field->getSetting("display_title",$field->getTitle());
        $dataType = $field->getSetting("data_type");
        if(is_a($field,"ADD_Field_Expression") && !$dataType) {
            return new NumberSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_Text_Date") || $dataType == "date") {
            return new DateSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_Text_DateTime") || $dataType == "datetime") {
            return new DateTimeSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_Text_Number") || $dataType == "number") {
            return new NumberSearchType($field,$fieldTitle);
        }

        if(is_a($field,"ADD_Field_Text") || $dataType == "text") {
            return new StringSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_List_MultiSelect")) {
            return new MultiListSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_Container_Autocomplete")) {
            return new AutocompleteSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_Checkbox")) {
            return new BooleanSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_List_YesNo")) {
            return new YesNoSearchType($field,$fieldTitle);
        }
        if(is_a($field,"ADD_Field_List")) {
            return new ListSearchType($field,$fieldTitle);
        }

        return new StringSearchType($field,$fieldTitle);
    }

}