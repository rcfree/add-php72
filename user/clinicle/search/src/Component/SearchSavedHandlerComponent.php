<?php

/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:30
 */
namespace ClinicLE\Search\Component;


class SearchSavedHandlerComponent extends \ADD_Component
{

    function getType() {
        return "SearchSaved";
    }
    function menuPath(&$menuPath)
    {
        $menuPath = "search/saved";
    }
    public function expectedPermissions(&$expPerms) {
        $expPerms[]="view";
    }
    public function title(&$title) {
        $title="Saved searches";
    }
}