<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;
use CLE;
use ClinicLE\Common\Util;
use ClinicLE\Search\Resource\SearchServices;


class SearchOptionsHandlerComponent extends \ADD_Component
{
    function getType() {
        return "SearchOptionsHandler";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="edit";
    }

    function beforePopulate() {
        $add = CLE::sys();
        $add->setState("new",false);
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $options = $ssData->getSearchData();
        $data = Util::val("options",$options,array());
        $this->getPage()->setData($data);
    }

    function submit($values) {
        $ssData = CLE::svc(SearchServices::SEARCH_SESSION_DATA);
        $searchData = $ssData->getSearchData();
        $searchData['options']=$values;
        $ssData->storeSearchData($searchData);
    }
}