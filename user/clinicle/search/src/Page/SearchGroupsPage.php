<?php

namespace ClinicLE\Search\Page;
use Symfony\Component\HttpFoundation\Request;
use CLE;
use ClinicLE\Search\Resource\SearchServices;

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchGroupsPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $ssData->initSearchData();

        return array("data"=>array_values($ssData->getSearchGroups()));
    }

    function createMenuPath()
    {
        return "search/groups";
    }

}