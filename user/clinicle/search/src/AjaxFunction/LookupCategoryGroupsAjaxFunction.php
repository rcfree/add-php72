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
class LookupCategoryGroupsAjaxFunction extends AbstractAjaxFunction {
    function runFunction(Request $request)
    {
        $sm = CLE::sys()->getParam("search_category");
        if(!$sm) {
            return;
        }
        $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);

        $optionList=$groupSet->getGroupsInCategory($sm);
        return array("search_group"=>array("options"=>$optionList));
    }

    function getInputFields()
    {
        return array("search_category");
    }

}