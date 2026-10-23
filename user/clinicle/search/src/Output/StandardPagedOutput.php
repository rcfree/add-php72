<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 08/08/17
 * Time: 11:58
 */

namespace ClinicLE\Search\Output;


use ClinicLE\Search\Event\ExportEvent;
use ClinicLE\Search\Event\SearchEvents;
use ClinicLE\System\Exception\NotifyUserException;

class StandardPagedOutput extends AbstractOutput
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
        $runner = $this->getRunner();
        $runner->setPageNo($this->getPageNo());
        if($runner->hasNoGroups()) {
            return array("data"=>array());
        }
        if($runner->hasNoAttributes()) {
            throw new NotifyUserException("No attributes have been selected");
        }
        $runner->prepareSelect();
        $dataFrame = $runner->runResults();
        $event = new ExportEvent($dataFrame);
        $runner->getEvents()->dispatch(SearchEvents::BEFORE_OUTPUT,$event);

        $data = array();
        $rows = $dataFrame->toMatrixOutput();
        if($rows==array(array())) {
            return array();
        }
        $firstRow = ($runner->getPageNo()-1)*$runner->getPageSize();
        $data['first']=$firstRow + 1;
        $data['last']=$firstRow + count($rows)-1;
        $data['rows_per_page']=$runner->getPageSize();
        $data['page_no']=$runner->getPageNo();
        $data['data']=$rows;
        $data['total']=$runner->runCount();
        return $data;
    }
}