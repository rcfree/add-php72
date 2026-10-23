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
class EncounterExtractorParams extends AbstractExtractorParams
{
    private $specialStatus;
    private $confirmedParentType = false;
    private $filters = array();
    private $filterGroup;
    private $showFilterLabels = false;
    private $pim;
    private $patientId;
    private $parentId;
    private $sortField;
    private $sortedAsDate=false;
    private $sortOrder="";

    /**
     * @return mixed
     */
    public function getSpecialStatus()
    {
        return $this->specialStatus;
    }

    /**
     * @param mixed $specialStatus
     */
    public function setSpecialStatus($specialStatus)
    {
        $this->specialStatus = $specialStatus;
    }

    /**
     * @return mixed
     */
    public function getConfirmedParentType()
    {
        return $this->confirmedParentType;
    }

    /**
     * @param mixed $confirmedParentType
     */
    public function setConfirmedParentType($confirmedParentType)
    {
        $this->confirmedParentType = $confirmedParentType;
    }

    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param mixed $filters
     */
    public function setFilterList($filterList)
    {
        $this->filters = array();
        foreach (explode("+", $filterList) as $filterItem) {
            $filter = $this->createFilter($filterItem, $this->filtergroup);
            $this->filters[] = $filter;
        }
    }

    /**
     * @return mixed
     */
    public function getFilterGroup()
    {
        return $this->filterGroup;
    }

    /**
     * @param mixed $filterGroup
     */
    public function setFilterGroup($filterGroup)
    {
        $this->filterGroup = $filterGroup;
    }

    /**
     * @return bool
     */
    public function doShowFilterLabels()
    {
        return $this->showFilterLabels;
    }

    /**
     * @param bool $showFilterLabels
     */
    public function setShowFilterLabels($showFilterLabels)
    {
        $this->showFilterLabels = $showFilterLabels;
    }

    /**
     * @return mixed
     */
    public function getSortField()
    {
        return $this->sortField;
    }

    /**
     * @param mixed $sortField
     */
    public function setSortField($sortField)
    {
        $this->sortField = $sortField;
    }

    /**
     * @return bool
     */
    public function isSortedAsDate()
    {
        return $this->sortedAsDate;
    }

    /**
     * @param bool $sortedAsDate
     */
    public function setSortedAsDate($sortedAsDate)
    {
        $this->sortedAsDate = $sortedAsDate;
    }

    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param mixed $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    function __construct($pim, $patientId=null, $parentId=null) {
        $this->pim = $pim;
        $this->patientId = $patientId;
        $this->parentId = $parentId;

    }

    /**
     * @return mixed
     */
    public function getPim()
    {
        return $this->pim;
    }

    /**
     * @return null
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * @return null
     */
    public function getParentId()
    {
        return $this->parentId;
    }


}