<?php

namespace ClinicLE\Data;
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 20/02/2017
 * Time: 20:16
 */
class PagedData
{
    private $data = array();
    private $pageNo;
    private $rowsPerPage;
    private $total;

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    function __construct($pageNo=1, $rowsPerPage=20) {
        $this->pageNo = $pageNo;
        $this->rowsPerPage = $rowsPerPage;
    }
    function outputData() {
        $firstRow = ($this->pageNo * $this->rowsPerPage) - $this->rowsPerPage;

        return array(
            "data"=>$this->data,
            "total"=>$this->total,
            "first"=>$firstRow + 1,
            "last"=>$firstRow + count($this->data),
            "rows_per_page"=>$this->rowsPerPage,
            "page_no"=>$this->pageNo
        );
    }
    function getLimitSQL() {
        $firstRow = (($this->pageNo-1) * $this->rowsPerPage);

        return "limit ".$firstRow.", ".$this->rowsPerPage;
    }
}