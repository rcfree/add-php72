<?php
/**
 * Created by PhpStorm.
 * User: rcf8
 * Date: 24/01/2017
 * Time: 08:52
 */

namespace ClinicLE\Search\Group;
use ClinicLE\Common\Util;
use Clinicle\Search\Query;
use ClinicLE\Search\SearchType\AbstractSearchType;
use ClinicLE\Search\Util\ParamSelectStatement;
use ClinicLE\Search\Query\GroupQuery;
use ClinicLE\Search\Util\WhereClause;

abstract class AbstractGroup
{
    private $name;
    private $title;
    private $prepareEnabled = false;
    private $aliasCount=0;
    private $hoursSince=23;

    /**
     * @return mixed
     */
    public function getHoursSince()
    {
        return $this->hoursSince;
    }

    /**
     * @param mixed $hoursSince
     */
    public function setHoursSince($hoursSince)
    {
        $this->hoursSince = $hoursSince;
    }

    /**
     * @return bool
     */
    public function isPrepareEnabled()
    {
        return $this->prepareEnabled;
    }

    /**
     * @param bool $prepareEnabled
     */
    public function setPrepareEnabled($prepareEnabled)
    {
        $this->prepareEnabled = $prepareEnabled;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    private $SearchTypes = array();
    private $categoryName;
    private $weight;

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param mixed $categoryName
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
    }

    /**
     * @return array
     */
    public function getSearchTypes()
    {
        return $this->SearchTypes;
    }

    /**
     * @param array $SearchTypes
     */
    public function setSearchTypes($SearchTypes)
    {
        $this->SearchTypes = $SearchTypes;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function addSearchType(AbstractSearchType $SearchType) {
        $SearchType->setGroup($this);
        $this->SearchTypes[$SearchType->getName()]=$SearchType;
    }

    /**
     * @param $type
     * @return AbstractSearchType
     */
    public function getSearchType($type) {
        return Util::val($type, $this->SearchTypes);
    }

    function populateSelect(ParamSelectStatement $select, GroupQuery $groupQuery) {
        foreach($groupQuery->getAttributes() as $attribute) {
            $SearchType = $this->getSearchType($attribute);
            if(!$SearchType) {
                throw new \Exception("No attribute with name ".$attribute." found");
            }
            $SearchType->addAttributeToSelect($select,$groupQuery);
        }
    }

    function populateWhere(ParamSelectStatement $select, GroupQuery $groupQuery) {
        $currFilter = 0;
        $logic = $groupQuery->getInternalLogic();
        $where = new WhereClause($logic);

        foreach($groupQuery->getFilters() as $filter) {
            $SearchType = $this->getSearchType($filter['field']);
            if(!$SearchType) {
                throw new \Exception("No filter type with name ".$filter['field']." found");
            }

            $SearchType->addFilterToWhere($where,$filter, $groupQuery);

            $currFilter++;
        }
        if($where->hasConditions()) {
            $select->addWhereClause($where, $groupQuery->getExternalLogic());
        }

    }
    function populateFrom(ParamSelectStatement $select, GroupQuery $groupQuery) { }
    function prepare(ParamSelectStatement $select, GroupQuery $groupQuery) {

    }
    function processRow($row, GroupQuery $query) {
        foreach($query->getAttributes() as $attribute) {

            $SearchType = $this->getSearchType($attribute);
            if(!$SearchType) {
                throw new \Exception("No attribute with name " . $attribute . " found");
            }

            $SearchType->processAttribute($row, $query);
        }
    }
    function getFieldsAsOptions() {
        $options = array();
        foreach($this->getSearchTypes() as $SearchType) {
            $options[$SearchType->getName()]=$SearchType->getFieldTitle();
        }
        return $options;
    }
    abstract function getTableExpression();

    function initQuery(GroupQuery $groupQuery) {

    }
    function incrementAliasCount() {
        $this->aliasCount++;
        return $this->aliasCount;
    }
    function getSectionType() {
        return "patient";
    }

    function getSectionName() {
        return "patient";
    }

    function getPatientIdField() {
        return "patient_id";
    }
    function getSectionIdField() {
        return "section_id";
    }
}