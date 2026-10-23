<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 18/04/2014
 * Time: 10:01
 */

class ADD_Field_OptionBuilder {
    function build($field) {
        $optionClass = "Data";

        if ($field->getSetting('dictionary')) {
            if($field->getSetting('lookup_field')) {
                $optionClass="DictionaryGroupItems";
            }
            else {
                $optionClass="DictionaryItems";
            }
        }
        if ($field->getSetting('dictionary_groups')) {
            $optionClass="DictionaryGroups";
        }
        if ($field->getSetting('dictionary_grouped')) {
            $optionClass="ItemByGroup";
        }
		$optionClass = $field->getSetting('dictionary_option',$optionClass);
        if(!$optionClass) throw new ADD_FieldException("OptionBuilder for field '".$field->getName()."' could not create an Option object");
        if(strstr($optionClass,"\\")) {
            $fo = ADD_Util::loadClass($optionClass);
        }
        else {
            $fo = ADD_Util::loadClass("ADD_Field_Option_" . $optionClass);
        }
        return $fo;
    }
} 