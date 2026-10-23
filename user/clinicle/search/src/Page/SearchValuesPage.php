<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Content\Render\HtmlElement;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\System\Service\CoreServices;
use CLE;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchValuesPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $query = $request->getRequest()->query;
        $groupId = $query->get("group");
        $session = CLE::svc(\ClinicLE\Search\Resource\SearchServices::SEARCH_SESSION_DATA);
        $group = $session->getGroupWithId($groupId);

        $searchType = $group->getSearchType($query->get("field"));
        $operation = $query->get("operation");
        if(!$operation) {
            return null;
        }
        $htmlElement = new HtmlElement();
        $searchType->renderOperationHtml($htmlElement,$operation);
        $script = CLE::web()->getJavascript("document");
        return array("data"=>$htmlElement->output(),"script"=>$script ? implode("\n",$script) : array());
    }

    function createMenuPath() {
        return "search/values";
    }

}