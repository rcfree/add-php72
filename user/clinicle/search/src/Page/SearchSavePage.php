<?php

namespace ClinicLE\Search\Page;
use ClinicLE\Common\Util;
use ClinicLE\Content\Render\HtmlElement;
use ClinicLE\Entity\Field\PageLinkField;
use Symfony\Component\HttpFoundation\Request;
use ClinicLE\Search\Query\QueryRunner;
use ClinicLE\Search\Query\DataQueryRunnerFactory;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Util\DelimitedDataWriter;
use ClinicLE\Security\AccessManager;
use ClinicLE\Service\Email\Email;
use ClinicLE\System\Service\CoreServices;
use CLE;
use ClinicLE\Search\Query\QueryExport;
use RandomLib\Factory;


/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:06
 */
class SearchSavePage extends \ClinicLE\Search\Page\AbstractSearchPage
{
    function processGetMethod(Request $request) {
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        if(!$ssData->hasSearchData()) {
            return array("success"=>false,"message"=>"No search data");
        }
        $id = $request->query->get("id");
        $title = $request->query->get("title");
        if(!$title) {
            $title="Untitled search";
        }
        $searchData = $ssData->getSearchData();

        $searchRepository = CLE::svc(SearchServices::SEARCH_REPOSITORY);

        $storedData = $searchRepository->storeSearchDefinition($id, $title, $searchData);
        return array("id"=>$storedData['id'],"title"=>$storedData['title'],"success"=>true,"action"=>Util::isEmpty
        ($id) ? "create"
            : "update");
    }

    function createMenuPath()
    {
        return "search/save";
    }

}