<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 11:58
 */

namespace ClinicLE\Search\Output;


class PatientPerRowOutput extends SearchExportOutput
{
    private $pageNo;

    /**
     * @return mixed
     */
    public function getPageNo()
    {
        return $this->pageNo;
    }

    /**
     * @param mixed $pageNo
     */
    public function setPageNo($pageNo)
    {
        $this->pageNo = $pageNo;
    }

    function outputResponse() {
        $response = parent::outputResponse();
        $content = $response->getOriginalContent();
        \CLE::info("content",$content->getFileName());
        return array("data"=>array());
    }
}