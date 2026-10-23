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
class SearchListPage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $ssRepo = CLE::svc(SearchServices::SEARCH_REPOSITORY);
        $user = CLE::sys()->getAccessManager()->getUser();
        return array("data"=>array_values($ssRepo->getSearchDefinitionsByUser($user)));
    }


    function createMenuPath()
    {
        return "search/list";
    }

}