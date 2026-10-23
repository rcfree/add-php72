<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 18/07/17
 * Time: 11:35
 */

namespace ClinicLE\Search\Page;


use Symfony\Component\HttpFoundation\Request;

abstract class AbstractSearchPage extends \ADD_Page_Api
{
    function apiOutput($values = array()) {
        $request = Request::createFromGlobals();
        $response = $this->processGetMethod($request);
        return $response;
    }
    abstract function processGetMethod(Request $request);
    function createMenuPath() {}

    function getComponents() {
        return new \ADD_ComponentCollection($this);
    }
    function hasComponent($name) {
        return false;
    }
    function getComponent($name) {

    }
    function afterSubmit(\ADD_Page $submitPage) {

    }
}