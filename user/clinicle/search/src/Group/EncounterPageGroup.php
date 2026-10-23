<?php
/**
 * Created by PhpStorm.
 * User: rob
 * Date: 24/01/17
 * Time: 15:52
 */

namespace ClinicLE\Search\Group;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Resource\SearchServices;
use ClinicLE\System\Service\CoreServices;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Search\Util\CreateTableSelectStatement;

class EncounterPageGroup extends AbstractPageGroup 
{
    private $pagePim;

    /**
     * @return mixed
     */
    public function getPagePim()
    {
        return $this->pagePim;
    }

    function __construct($page) {
        parent::__construct($page);
        $this->setEncounterType($page->getSetting("encounter"));
        $this->pagePim = $page->getPim();
    }

    /**
     * @return mixed
     */
    public function getEncounterType()
    {
        return $this->encounterType;
    }

    /**
     * @param mixed $encounterType
     */
    public function setEncounterType($encounterType)
    {
        $this->encounterType = $encounterType;
    }
    function populateSelect(ParamSelectStatement $select, GroupQuery $query)
    {
        $settings = $query->getSettings();

        if ($settings->get("include_episode_id") == 1) {
            $select->addToSelect($query->getTableAlias() . ".episode_id as `" . $this->getName() . ".episode_id`");
        }
        if ($settings->get("include_section_id") == 1 || $query->getRunner()->getSearchOptions()->get("patient_per_row")) {
            $select->addToSelect($query->getTableAlias() . ".parent_encounter_id as  `" . $this->getName() . ".section_id`");
        }
        if ($settings->get("include_date_created") == 1) {
            $select->addToSelect($query->getTableAlias() . ".date_created as  `" . $this->getName() . ".date_created`");
        }
        if ($settings->get("include_date_updated") == 1) {
            $select->addToSelect($query->getTableAlias() . ".date_updated as  `" . $this->getName() . ".date_updated`");
        }

        parent::populateSelect($select, $query);
    }
    function populateFrom(ParamSelectStatement $select, GroupQuery $groupQuery)
    {
        $select->addGroupJoin($groupQuery);
    }

    function populateWhere(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $settings = $groupQuery->getSettings();
        if($settings->get("count_agg")==1) {
            foreach($groupQuery->getAttributes() as $attr) {
                $searchType = $this->getSearchType($attr);
                $select->addToSelect("count(" . $groupQuery->getTableAlias() . ".id) as `Count of "
                    .$searchType->getFieldAlias($groupQuery)."`");
                $select->addGroupBy($groupQuery->getTableAlias() . "." . $attr);
                //$select->setHaving("count(" . $query->getTableAlias() . ".id")>0 and ".$query->getTableAlias() . "." . $attr . " is not null");
            }
        }
        parent::populateWhere($select,$groupQuery);
    }
    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $this->initSearchTypes();
        if(!$this->isPrepareEnabled()) {
            return;
        }
        $searchRepo = \CLE::svc(SearchServices::SEARCH_REPOSITORY);

        $searchRepo->dropTable($this->getTableExpression());

        $searchRepo->createObservationTable($this);
        $searchRepo->addEncounterIndexes($this->getTableExpression());
    }

    function getTableExpression() {
        return "e_".preg_replace("[-]","_",$this->getEncounterType());
    }

    function getSectionType() {
        return $this->getParentPage()->getSetting("encounter");
    }

    function getSectionName() {
        return $this->getParentPage()->getPim();
    }

}
