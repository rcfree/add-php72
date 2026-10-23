<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;


use ClinicLE\Common\Util;
use ClinicLE\Search\Resource\SearchServices;

class SearchDefinitionHandlerComponent extends \ADD_Component
{
    function beforePopulate() {
        $id = \CLE::sys()->getParam("id");
        $action = \CLE::sys()->getParam("action");


        $ssSession = \CLE::svc(SearchServices::SEARCH_SESSION_DATA);

        if($action=="create") {
            $ssSession->initSearchData(true);
            return;
        }

        if(!$id) {
            return;
        }
        $ssRepo = \CLE::svc(SearchServices::SEARCH_REPOSITORY);
        if($action=="delete") {
            $ssRepo->deleteSearchDefinitionById($id);
            return;
        }
        $search = $ssRepo->getSearchDefinitionById($id);

        $searchData = Util::val("data",$search);
        $searchData['title']=$search['title'];
        $ssSession->storeSearchData($searchData);
        $data = array("search_title"=>$search['title'], "search_id"=>\CLE::sys()->getParam('id'));
        $this->getPage()->setData($data);
    }
    function getType() {
        return "SearchDefinition";
    }
    function menuPath(&$menuPath)
    {
        $menuPath = "search/definition";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="view";
        $expPerms[]="create";
        $expPerms[]="edit";
    }
    public function title(&$title) {
        $title="Advanced search";
    }
    function baseContent() {

    }

}
