<?php
namespace ClinicLE\Client\Data;
use ClinicLE\Data\Query\AbstractEncounterQuery;
use ClinicLE\Data\Query\QueryRunner;
use CLE;
use ADD_Database_Encounter as EncounterDatabase;
use ClinicLE\Common\Util;
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 05/07/17
 * Time: 12:19
 */
class AbstractExtractorParams
{
    private $rowsPerPage;
    private $pageNo;

    /**
     * @return mixed
     */
    public function getRowsPerPage()
    {
        return $this->rowsPerPage;
    }

    /**
     * @param mixed $rowsPerPage
     */
    public function setRowsPerPage($rowsPerPage)
    {
        $this->rowsPerPage = $rowsPerPage;
    }

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

}