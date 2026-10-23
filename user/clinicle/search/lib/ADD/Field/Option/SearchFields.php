<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 13:29
 */
class ADD_Field_Option_SearchFields extends ADD_Field_Option_Standard
{
    function initOptions($field, $page)
    {
        $groupId = CLE::sys()->getParam("parentid");

        $session = CLE::svc(\ClinicLE\Search\Resource\SearchServices::SEARCH_SESSION_DATA);
        $groupData = $session->getSearchGroup($groupId);
        $groupName = $groupData['name'];
        $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);

        $group = $groupSet->getGroup($groupName);
        if(!$group) {
            return;
        }
        $this->_optionList=$group->getFieldsAsOptions();
    }
}