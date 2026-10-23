<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 26/01/17
 * Time: 14:35
 */

namespace ClinicLE\Search\Field;


class AttrOperationsField extends \ADD_Field_List_Dropdown
{
    function getHtmlFieldAttrs()
    {
        $attrs = parent::getHtmlFieldAttrs();
        $attrs['ng-change']="updateValues()";
        $attrs['ng-model']="fields.".$this->getName();
        return $attrs;
    }
}