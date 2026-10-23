<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 13:29
 */
class ADD_Field_Option_SearchCategories extends ADD_Field_Option_Standard
{
    function initOptions($field, $page)
    {
        $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);
        $this->_optionList=$groupSet->getCategories();
    }
}