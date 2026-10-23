<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 16/11/2016
 * Time: 17:27
 */

namespace ClinicLE\Page;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractApiPage extends AbstractWebServicePage {
    function createMenuPath() {
        return "api/".$this->getName();
    }
    function processRequest(Request $request) {
        $values = $request->request->all();

        $data = $this->apiOutput($values);
        if(!isset($data['data'])) {
            $data=array("data"=>$data);
        }
        return $data;
    }

    abstract function apiOutput();

}
