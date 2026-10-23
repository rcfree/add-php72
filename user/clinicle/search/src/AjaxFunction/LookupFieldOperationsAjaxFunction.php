<?php
namespace ClinicLE\Search\AjaxFunction;
use ClinicLE\Ajax\AbstractAjaxFunction;
use Symfony\Component\HttpFoundation\Request;
use CLE;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 13:43
 */
class LookupFieldOperationsAjaxFunction extends AbstractAjaxFunction {
    function runFunction(Request $request)
    {
        $fieldName = CLE::sys()->getParam("filter_field");

        if(!$fieldName) {
            return;
        }
        $groupId = CLE::sys()->getParam("__group");

        $session = CLE::svc(\ClinicLE\Search\Resource\SearchServices::SEARCH_SESSION_DATA);
        $groupData = $session->getSearchGroup($groupId);
        $groupName = $groupData['name'];
        $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);
        $group = $groupSet->getGroup($groupName);
        $SearchType = $group->getSearchType($fieldName);

        $optionList = $SearchType->getOperationsAsPairs();
        return array("operation"=>array("options"=>$optionList));
    }

    function getInputFields()
    {
        return array("filter_field");
    }

}