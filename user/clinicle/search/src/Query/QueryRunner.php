<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 09:57
 */

namespace ClinicLE\Search\Query;

use ClinicLE\Common\Config\AbstractConfig;
use ClinicLE\Module\Exception\ItemUndefinedException;
use ClinicLE\Search\Group\AbstractPatientPageGroup;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\Search\Util\DataFrame;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\System\Event\EventDispatcher;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\Group\GroupSet;
use ClinicLE\Search\Group\AbstractGroup;

class QueryRunner
{
    /**
     * @var array
     */
    private $groupQueries = array();
    private $groupSet;
    private $pageNo=1;
    private $pageSize=50;
    private $select = null;
    private $searchOptions;
    private $events;

    /**
     * @return EventDispatcher
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return ParamSelectStatement
     */
    public function getSelect()
    {
        return $this->select;
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

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param mixed $pageSize
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;
    }

    function __construct(GroupSet $groupSet, EventDispatcher $events) {
        $this->groupSet = $groupSet;
        $this->events = $events;
    }
    function newGroupQuery($groupName, $settings = array())
    {
        $group = $this->groupSet->getGroup($groupName);
        if(!$group) {
            throw new ItemUndefinedException("Group '$groupName' does not exist in set");
        }
        $groupQuery = new GroupQuery($group, $settings, $this);
        $group->initQuery($groupQuery);
        $this->groupQueries[] = $groupQuery;
        return $groupQuery;
    }
    function hasNoGroups() {
        return count($this->groupQueries)===0;
    }
    function hasNoAttributes() {
        $attrCount = 0;

        foreach ($this->groupQueries as $groupQuery) {
            $attrCount += count($groupQuery->getAttributes());
        }

        return $attrCount===0;
    }
    function prepareSelect($forcePrepare=false)
    {
        if($this->select && !$forcePrepare) {
            return;
        }
        $this->select = new ParamSelectStatement();

        if($this->getSearchOptions()->get("non_distinct_values")==1) {
            $this->select->setDistinct(false);
        }
        else {
            $this->select->setDistinct(true);
        }
        if ($this->getSearchOptions()->get("patient_per_row")) {
            $this->select->addToSelect(AbstractPatientPageGroup::PATIENT_ALIAS.".id as  `patient_id`");
        }

        foreach ($this->groupQueries as $groupQuery) {
            if (!$groupQuery->isIncluded() || !$groupQuery->isEnabled()) {
                continue;
            }
            
            $group = $groupQuery->getGroup();
            $group->prepare($this->select, $groupQuery);
        }
        foreach ($this->groupQueries as $groupQuery) {
            if(!$groupQuery->isEnabled()) {
                continue;
            }

            $group = $groupQuery->getGroup();
            $group->populateSelect($this->select, $groupQuery);
        }
        $selectGqs = $this->groupQueries;
/*
        usort($selectGqs,function($a, $b) {
            $aweight = $a->getSettings()->get("from_weight");
            $bweight = $b->getSettings()->get("from_weight");

            if($aweight==$bweight) {
                return 0;
            }
            return ($aweight<$bweight) ? -1 : 1;
        });
*/
        foreach ($selectGqs as $groupQuery) {
            if(!$groupQuery->isEnabled()) {
                continue;
            }

            $group = $groupQuery->getGroup();
            $group->populateFrom($this->select, $groupQuery);
        }
        foreach ($this->groupQueries as $groupQuery) {
            if(!$groupQuery->isEnabled()) {
                continue;
            }

            $group = $groupQuery->getGroup();
            $group->populateWhere($this->select, $groupQuery);
        }

        if ($this->getPageNo()) {
            $limit = $this->determineLimit();
            $this->select->setLimit($limit);
        }
    }
    function runResults($forcePrepare=false) {
        $this->prepareSelect($forcePrepare);
        $mainConn = \CLE::svc(SearchServices::SEARCH_REPOSITORY)->getConnection();

        $sql = $this->select->generateFullSql();
        \CLE::info("sql:",$sql);
        \CLE::info("params:",$this->select->getParams());
        $rows = $mainConn->fetchAll($sql,$this->select->getParams());

        $dataFrame = new DataFrame();
        foreach($rows as $row) {
            $row = new \ArrayObject($row);
            foreach($this->groupQueries as $groupQuery) {
                if(!$groupQuery->isEnabled()) {
                    continue;
                }
                $group = $groupQuery->getGroup();
                $group->processRow($row,$groupQuery);
            }
            $dataFrame->addRow($row);
        }
        return $dataFrame;
    }

    function runCount($forcePrepare=false) {
        $this->prepareSelect($forcePrepare);
        $mainConn = \CLE::svc(SearchServices::SEARCH_REPOSITORY)->getConnection();

        return $mainConn->fetchSingleValue($this->select->generateCountSql(),$this->select->getParams());
    }
    function determineLimit() {
        return ($this->getPageNo()-1)*$this->getPageSize().",".$this->getPageSize();
    }

    /**
     * @return mixed
     */
    public function getSearchOptions()
    {
        return $this->searchOptions;
    }

    /**
     * @param mixed $searchOptions
     */
    public function setSearchOptions($searchOptions)
    {
        $this->searchOptions = $searchOptions;
    }

    /**
     * @return array
     */
    public function getGroupQueries()
    {
        return $this->groupQueries;
    }

    /**
     * @return GroupQuery
     */
    public function getGroupQueryByName($groupName) {
        foreach($this->groupQueries as $groupQuery) {
            if($groupQuery->getGroup()->getName()!=$groupName) {
                continue;
            }
            return $groupQuery;
        }
        return null;
    }

}
