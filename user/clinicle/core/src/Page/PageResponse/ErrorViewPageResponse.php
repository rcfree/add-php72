<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 25/01/17
 * Time: 10:16
 */

namespace ClinicLE\Page\PageResponse;


class ErrorViewPageResponse extends ViewPageResponse
{
    function __construct(\Exception $exception) {
        parent::__construct("page_error",array("error"=>$exception),200);
    }

}