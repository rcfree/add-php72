<?php
namespace ClinicLE\Search\AjaxFunction;
use ClinicLE\Ajax\AbstractAjaxFunction;
use ClinicLE\Content\Render\HtmlElement;
use Symfony\Component\HttpFoundation\Request;
use CLE;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 13:43
 */
class LookupFieldValuesAjaxFunction extends AbstractAjaxFunction {
    function runFunction(Request $request)
    {
        $fieldName = CLE::sys()->getParam("filter_field");

        if(!$fieldName) {
            return;
        }
        $op = CLE::sys()->getParam("operation");

        if(!$op) {
            return;
        }
        $groupId = CLE::sys()->getParam("__group");

        $session = CLE::svc(\ClinicLE\Search\Resource\SearchServices::SEARCH_SESSION_DATA);
        $groupData = $session->getSearchGroup($groupId);
        $groupName = $groupData['name'];
        $groupSet = CLE::svc(\ClinicLE\Search\Resource\SearchServices::GROUP_SET);
        $group = $groupSet->getGroup($groupName);
        $searchType = $group->getSearchType($fieldName);

        $htmlElement = new HtmlElement();
        $searchType->renderOperationHtml($htmlElement, $op);
        $script = CLE::web()->getJavascript("document");
        return array("values_container"=>array("content"=>$htmlElement->output(),"script"=>$script ? implode("\n",$script) : array()));
    }

    function getInputFields()
    {
        return array("filter_field","operation");
    }

}