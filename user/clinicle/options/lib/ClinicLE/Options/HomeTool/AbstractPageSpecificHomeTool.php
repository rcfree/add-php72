<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 15/09/16
 * Time: 15:44
 */

namespace ClinicLE\Options\HomeTool;


abstract class AbstractPageSpecificHomeTool extends AbstractHomeTool
{
    private $page;

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param mixed $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

}